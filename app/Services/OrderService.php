<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method

        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();
        $affiliate = Affiliate::where('merchant_id', $merchant->id)->first();
        $commissionRate = 0.00;
        if ($affiliate) {
            $commissionRate = $data['subtotal_price'] * $affiliate->commission_rate;
        }

        $user = User::where('email', $data['customer_email'])->first();
        if (!$user) {

            $newAffiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], 0.1);
        }


        $order = Order::updateOrCreate([
            "external_order_id" => $data['order_id']
        ], [
            'subtotal' => $data['subtotal_price'],
            'affiliate_id' => $affiliate->id ?? NULL,
            'merchant_id' => $merchant->id,
            'commission_owed' =>  $commissionRate,
            'discount_code' => $data["discount_code"],
            'external_order_id' => $data['order_id'],
        ]);

        \Log::info($commissionRate);

        echo "Commision: " . $commissionRate . PHP_EOL;
    }
}
