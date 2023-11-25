<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\TryCatch;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method

        try {
            if (User::where('email', $email)->exists()) {
                throw new AffiliateCreateException('Email address is already reggistered.');
            }
            $discountCode = $this->apiService->createDiscountCode($merchant);
            $createUser = User::updateOrCreate([
                "name" => $name,
                "email" => $email,
                "type" => User::TYPE_AFFILIATE
            ]);

            $affiliate = Affiliate::create([
                "user_id" =>  $createUser->id,
                "merchant_id" => $merchant->id,
                "commission_rate" => $commissionRate,
                "discount_code" => $discountCode['code']
            ]);

            Mail::to($createUser->email)->send(new AffiliateCreated($affiliate));

            return $affiliate;
        } catch (\Exception $th) {
            throw  $th;
        }

    }
}
