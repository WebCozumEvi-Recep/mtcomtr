<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AffiliateCommission;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $withholdingRate = (float) Setting::val('affiliate_withholding_rate', 20.0);
        $vatRate = (float) Setting::val('affiliate_vat_rate', 20.0);

        $commissions = AffiliateCommission::whereIn('status', ['pending', 'approved', 'withdrawing'])->get();

        foreach ($commissions as $commission) {
            $gross = (float) $commission->gross_commission;
            $taxType = $commission->tax_type;

            $withholding = 0.00;
            $vat = 0.00;
            $net = $gross;

            if ($taxType === 'individual') {
                $withholding = round($gross * ($withholdingRate / 100), 2);
                $net = round($gross - $withholding, 2);
            } elseif ($taxType === 'company') {
                $vat = round($gross * ($vatRate / 100), 2);
                $net = round($gross + $vat, 2);
            }

            $commission->update([
                'withholding_amount' => $withholding,
                'vat_amount' => $vat,
                'net_amount' => $net
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback required for data correction
    }
};
