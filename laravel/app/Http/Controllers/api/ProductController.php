<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::with([
            'platformProducts.platform', 
            'platformProducts.priceHistories'
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }

    public function getAnalytics(string $barcode): JsonResponse
    {
        //looking for a basic barcode product
        $product = Product::where('barcode', $barcode)
            ->with(['platformProducts.platform', 'platformProducts.priceHistories' => function($query) {
                $query->orderBy('created_at', 'asc'); 
            }])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'The product with this barcode was not found in the database'
            ], 404);
        }

        $marketplaces = [];
        $cheapestPrice = null;
        $cheapestPlatform = null;

        foreach ($product->platformProducts as $pp) {
            $currentPrice = (float)$pp->current_price;
            $history = $pp->priceHistories;

            // looking for the lowest price on the market right now
            if (is_null($cheapestPrice) || $currentPrice < $cheapestPrice) {
                $cheapestPrice = $currentPrice;
                $cheapestPlatform = $pp->platform->name;
            }

            // Compare the first historical price and the current one
            $trendStatus = 'Stable';
            $percentChange = 0;

            if ($history->count() >= 2) {
                $firstPrice = (float)$history->first()->price;
                $latestPrice = (float)$history->last()->price;

                if ($firstPrice > 0) {
                    $percentChange = round((($latestPrice - $firstPrice) / $firstPrice) * 100, 2);
                    
                    if ($percentChange < 0) {
                        $trendStatus = 'The price has decreased (Discount ' . abs($percentChange) . '%)';
                    } elseif ($percentChange > 0) {
                        $trendStatus = 'The price has increased (Rise in price ' . $percentChange . '%)';
                    }
                }
            }

            // Turning dates and prices into X and Y
            $chartData = $history->map(function ($h) {
                return [
                    'date' => $h->created_at->format('d.m'), // X
                    'price' => (float)$h->price              // Y
                ];
            })->values()->all();

            $marketplaces[] = [
                'platform_name' => $pp->platform->name,
                'url' => $pp->url,
                'current_price' => $currentPrice,
                'old_price' => $pp->old_price ? (float)$pp->old_price : null,
                'analytics' => [
                    'trend' => $trendStatus,
                    'change_percent' => $percentChange,
                    'total_scraped_records' => $history->count()
                ],
                'chart_points' => $chartData
            ];
        }

        // Response
        return response()->json([
            'success' => true,
            'product' => [
                'title' => $product->title,
                'barcode' => $product->barcode,
            ],
            'market_comparison' => [
                'best_deal_platform' => $cheapestPlatform,
                'minimal_market_price' => $cheapestPrice . ' BYN',
                'generated_at' => now()->format('Y-m-d H:i:s')
            ],
            'platforms' => $marketplaces
        ], 200);
    }
}
