<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VakifbankPaymentService
{
    protected $provider;
    protected $credentials;

    public function __construct(PaymentProvider $provider)
    {
        $this->provider = $provider;
        $this->credentials = $provider->config; // JSON array
    }

    /**
     * Process 3D Payment (Redirect)
     */
    public function process(Order $order, array $cardData)
    {
        $isTest = ($this->credentials['mode'] ?? 'test') === 'test';
        $apiUrl = $isTest 
            ? ($this->credentials['api_demo_url'] ?? 'https://3dsecuretest.vakifbank.com.tr/MPIAPI/MPI_Enrollment.aspx')
            : ($this->credentials['api_url'] ?? 'https://3dsecure.vakifbank.com.tr/MPIAPI/MPI_Enrollment.aspx');

        $terminalId = $this->credentials['terminal_id'] ?? '';
        $merchantId = $this->credentials['merchant_id'] ?? '';
        $password = $this->credentials['password'] ?? '';
        
        $amount = number_format($order->grand_total, 2, '.', '');
        $expiry = str_replace([' ', '/'], '', $cardData['cc_expiry']);
        // Vakifbank expects YYMM
        $expiry = substr($expiry, 2, 2) . substr($expiry, 0, 2);

        // Determine Brand Name (100 = Visa, 200 = Mastercard, 300 = Amex)
        $brandName = '100'; // Default
        $ccPrefix = substr($cardData['cc_number'], 0, 1);
        if ($ccPrefix === '5') {
            $brandName = '200';
        } elseif ($ccPrefix === '3') {
            $brandName = '300';
        }

        // Prepare parameters for MPI Enrollment API
        $uniqueId = $order->order_number;

        $params = [
            'MerchantId' => $merchantId,
            'MerchantPassword' => $password,
            'VerifyEnrollmentRequestId' => $uniqueId,
            'Pan' => $cardData['cc_number'],
            'ExpiryDate' => $expiry,
            'CardHoldersName' => $cardData['cc_name'],
            'KartCvv' => $cardData['cc_cvc'],
            'PurchaseAmount' => $amount,
            'Currency' => '949',
            'BrandName' => $brandName,
            'SuccessUrl' => route('payment.callback'),
            'FailureUrl' => route('payment.callback'),
            'SessionInfo' => $uniqueId,
            'OrderId' => $uniqueId,
            'VerifyType' => '3D_Pay'
        ];

        Log::info('Vakifbank MPI API Initiated', [
            'api_url' => $apiUrl,
            'order_no' => $params['VerifyEnrollmentRequestId']
        ]);

        try {
            $response = Http::asForm()->post($apiUrl, $params);
            $xmlString = $response->body();
            
            Log::info('Vakifbank MPI API Response', [
                'order_no' => $params['VerifyEnrollmentRequestId'],
                'response' => $xmlString
            ]);

            $xml = simplexml_load_string($xmlString);

            if ($xml === false) {
                throw new \Exception("Invalid XML response from bank API.");
            }

            $errorCode = isset($xml->MessageErrorCode) ? (string)$xml->MessageErrorCode : '0';
            if ($errorCode !== '' && !in_array($errorCode, ['0', '00', '200'])) {
                $errorMsg = isset($xml->ErrorMessage) ? (string)$xml->ErrorMessage : 'Unknown API Error';
                throw new \Exception("Vakıfbank API Error: " . $errorMsg . " (Code: " . $errorCode . ")");
            }

            if (isset($xml->Message->VERes->Status)) {
                $status = (string)$xml->Message->VERes->Status;
                
                if ($status === 'Y') {
                    $acsUrl = (string)$xml->Message->VERes->ACSUrl;
                    $paReq = (string)$xml->Message->VERes->PaReq;
                    $termUrl = (string)$xml->Message->VERes->TermUrl;
                    $md = (string)($xml->Message->VERes->MD ?? $params['VerifyEnrollmentRequestId']);
                    
                    if (empty($termUrl)) {
                        $termUrl = route('payment.callback');
                    }

                    // Create an auto-submitting HTML form to ACSUrl
                    $html = "<html><head><title>3D Secure Redirect</title></head><body onload='document.forms[0].submit()'>";
                    $html .= "<form action='{$acsUrl}' method='post'>";
                    $html .= "<input type='hidden' name='PaReq' value='{$paReq}'>";
                    $html .= "<input type='hidden' name='TermUrl' value='{$termUrl}'>";
                    $html .= "<input type='hidden' name='MD' value='{$md}'>";
                    
                    // Add any other dynamic parameters from VERes
                    foreach ($xml->Message->VERes->children() as $key => $value) {
                        if (!in_array($key, ['Status', 'ACSUrl', 'PaReq', 'TermUrl', 'MD'])) {
                            $val = (string)$value;
                            if (!empty($val)) {
                                $html .= "<input type='hidden' name='{$key}' value='{$val}'>";
                            }
                        }
                    }
                    
                    $html .= "</form></body></html>";

                    return [
                        'success' => true,
                        'redirect_html' => $html,
                        'is_redirect' => true
                    ];
                } else {
                    $errorDetails = "Status: " . $status;
                    if (isset($xml->ErrorMessage)) {
                        $errorDetails .= " - " . (string)$xml->ErrorMessage;
                    }
                    throw new \Exception("Card is not enrolled in 3D Secure or error occurred. " . $errorDetails);
                }
            } else {
                throw new \Exception("Unexpected XML response structure: Status missing.");
            }

        } catch (\Exception $e) {
            Log::error('Vakifbank 3D Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
