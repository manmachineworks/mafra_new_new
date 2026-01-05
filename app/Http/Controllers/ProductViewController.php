<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductViewController extends Controller
{
    /**
     * Upsert the current viewer and return today's views and live viewers.
     */
    public function viewStats(Request $request, Product $product)
    {
        $now = Carbon::now();
        $ip = $request->ip();
        $today = $now->toDateString();

        DB::table('product_views')->updateOrInsert(
            [
                'product_id' => $product->id,
                'ip_address' => $ip,
                'view_date' => $today,
            ],
            [
                'last_seen_at' => $now,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $todayViews = DB::table('product_views')
            ->where('product_id', $product->id)
            ->whereDate('view_date', $today)
            ->count();

        $liveViewers = DB::table('product_views')
            ->where('product_id', $product->id)
            ->where('last_seen_at', '>=', $now->copy()->subMinutes(5))
            ->count();

        return response()->json([
            'today_views' => $todayViews,
            'live_viewers' => $liveViewers,
        ]);
    }
}
