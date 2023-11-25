<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {
    }

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method

        $from = $request->from;
        $to = $request->to;
        // $merchants = Merchant::withCount('orders')->withSum('orders', function ($query) {
        //     $query->where('payout_status', 'unpaid');
        // })->get();
        $merchants = Merchant::all();
        $response = [];
        $merchant = Merchant::where('user_id', auth()->user()->id)->first();
        $orders = Order::where('merchant_id', $merchant->id)->whereBetween('created_at', [$from, $to]);
        $response['count'] = $orders->count();
        $response['revenue'] = $orders->sum('subtotal');
        $response['commission_owed'] = $orders->whereNotNull('affiliate_id')->sum('commission_owed');


        return response()->json([
            "count" => $response['count'],
            "revenue" => $response['revenue'],
            "commissions_owed" => $response['commission_owed'],
        ]);
    }
}
