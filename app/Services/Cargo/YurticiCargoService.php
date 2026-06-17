<?php

namespace App\Services\Cargo;

use App\Models\Order;
use App\Models\CargoSetting;
use Exception;
use SoapClient;
use SoapFault;

class YurticiCargoService
{
    protected ?CargoSetting $setting = null;

    public function __construct()
    {
        $this->setting = CargoSetting::where('carrier_name', 'yurtici')->first();
    }

    /**
     * @param \App\Models\Order $order
     * @return array
     */
    public function createShipment(Order $order): array
    {
        if (!$this->setting || !$this->setting->is_active) {
            throw new Exception('Yurtiçi Kargo ayarları aktif değil.');
        }

        if (!$this->setting->api_username || !$this->setting->api_password) {
            throw new Exception('Yurtiçi API kullanıcı adı veya şifre tanımlı değil (cargo_settings).');
        }

        $order->loadMissing('customer');

        $wsdl = $this->setting->is_test_mode
            ? 'http://testwebservices.yurticikargo.com:9090/KOPSWebServices/ShippingOrderDispatcherServices?wsdl'
            : 'http://webservices.yurticikargo.com:8080/KOPSWebServices/ShippingOrderDispatcherServices?wsdl';

        [$webUser, $webPass] = $this->resolveCredentials($order);

        ini_set('soap.wsdl_cache_enabled', '0');

        $soapOptions = [
            'trace' => 0,
            'exceptions' => true,
            'encoding' => 'UTF-8',
            'connection_timeout' => 60,
            'stream_context' => stream_context_create([
                'http' => ['timeout' => 120],
            ]),
        ];

        try {
            $soapClient = new SoapClient($wsdl, $soapOptions);

            $shippingOrders = [];
            $shippingOrders[] = $this->buildShippingOrderVo($order);

            $response = $soapClient->createShipment([
                'ShippingOrderVO' => $shippingOrders,
                'wsUserName' => $webUser,
                'wsPassword' => $webPass,
                'userLanguage' => 'TR',
            ]);

            $normalized = json_decode(json_encode($response), true) ?? [];
            $trackingNumber = $this->extractTrackingNumber($normalized);

            if ($trackingNumber === null) {
                $err = $this->extractError($normalized);
                throw new Exception(
                    $err ?? 'Yurtiçi: onay dönmedi (count≠1 veya cargoKey yok).'
                );
            }

            return [
                'status' => 'success',
                'tracking_number' => $trackingNumber,
                'response' => $normalized,
            ];
        } catch (SoapFault $e) {
            throw new Exception('Yurtiçi SOAP: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Yurtiçi Kargo: ' . $e->getMessage());
        }
    }

    /**
     * Eski kargolar tablosu: tahsilatta ayrı kullanıcı (api_cod_*).
     */
    private function resolveCredentials(Order $order): array
    {
        $isCod = (float) $order->grand_total > 0;
        if (
            $isCod
            && !empty($this->setting->api_cod_username)
            && !empty($this->setting->api_cod_password)
        ) {
            return [$this->setting->api_cod_username, $this->setting->api_cod_password];
        }

        return [$this->setting->api_username, $this->setting->api_password];
    }

    /**
     * Eski YurtIciEntegre.php createShipment (islem==1) ile aynı nesne mantığı.
     */
    /**
     * @param \App\Models\Order $order
     * @return \stdClass
     */
    private function buildShippingOrderVo(Order $order): \stdClass
    {
        $vo = new \stdClass();
        $customer = $order->customer;
        $orderNo = $order->order_number;

        $vo->cargoKey = $orderNo;
        $vo->invoiceKey = $orderNo;
        $vo->waybillNo = $orderNo;
        $vo->description = $orderNo . ' Numaralı Sipariş';
        $vo->desi = '0';
        $vo->kg = '0';
        $vo->specialField1 = '1$' . $order->id . '#';
        $vo->cargoCount = '1';

        $vo->receiverCustName = $customer?->full_name ?? '-';
        $vo->receiverAddress = $order->address ?? '-';
        $vo->receiverPhone1 = $customer?->phone ?? '-';
        $vo->receiverPhone2 = $customer?->phone ?? '';
        $vo->receiverPhone3 = '';
        $vo->cityName = $order->city ?? '-';
        $vo->townName = $order->district ?? '-';
        $vo->taxOfficeId = 0;
        $vo->taxNumber = '';
        $vo->taxOfficeName = '';

        if ((float) $order->grand_total > 0) {
            $vo->ttCollectionType = '0';
            $vo->ttDocumentSaveType = '1';
            $vo->ttInvoiceAmount = $this->formatCodAmount($order->grand_total);
            $vo->ttDocumentId = sprintf('%010d', (int) $order->id);
            $vo->orgReceiverCustId = (string) ($customer?->id ?? 0);
            $vo->dcSelectedCredit = null;
            $vo->dcCreditRule = null;
        } else {
            $vo->ttDocumentId = null;
            $vo->dcSelectedCredit = null;
            $vo->dcCreditRule = null;
        }

        return $vo;
    }

    private function formatCodAmount($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    private function extractTrackingNumber(array $normalized): ?string
    {
        $vo = $normalized['ShippingOrderResultVO'] ?? null;
        if (!is_array($vo)) {
            return null;
        }

        $count = isset($vo['count']) ? (int) $vo['count'] : 0;
        if ($count !== 1) {
            return null;
        }

        $detail = $vo['shippingOrderDetailVO'] ?? null;
        if (!is_array($detail)) {
            return null;
        }

        $cargoKey = $detail['cargoKey'] ?? null;

        return $cargoKey !== null && $cargoKey !== '' ? (string) $cargoKey : null;
    }

    private function extractError(array $normalized): ?string
    {
        $vo = $normalized['ShippingOrderResultVO'] ?? null;
        if (!is_array($vo)) {
            return null;
        }
        $detail = $vo['shippingOrderDetailVO'] ?? null;
        if (!is_array($detail)) {
            return null;
        }
        $code = $detail['errCode'] ?? '';
        $msg = $detail['errMessage'] ?? '';

        if ($msg === '' && $code === '') {
            return null;
        }

        return trim($code . ' ' . $msg);
    }
}
