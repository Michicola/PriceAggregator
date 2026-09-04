<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\PlatformProduct;
use App\Services\Parsers\PriceParserManager;
use Exception;
use Illuminate\Http\JsonResponse;

class PriceParserController extends Controller
{
    protected PriceParserManager $priceParserManager; 

    public function __construct(PriceParserManager $parser)
    {
        $this->priceParserManager = $parser;
    }

    public function updatePrices(): JsonResponse
    {
        $platformProducts = PlatformProduct::with(['product','platform'])->get();
        $report =[];

        foreach($platformProducts as $item){
            try{
                $strategy = $this->priceParserManager->getStrategy($item);

                $parserData = $strategy->parse($item);

                if(isset($parserData['skipped']) && $parserData['skipped'] === true){
                    $report[] = [
                        'platform' => $item->platform->name,
                        'product' => $item->product->title,
                        'status' => 'skipped',
                        'message' => 'The price for this platform has already been updated today'
                    ];
                    continue;
                }

                $oldPrice = $item->current_price;
                $newPrice = $parserData['current_price'];

                if ($oldPrice != $newPrice) {
                    $item->priceHistories()->create([
                        'price' => $newPrice
                    ]);
                }

                $item->update([
                    'current_price' => $newPrice,
                    'old_price' => $parserData['old_price']
                ]);

                $report[] = [
                    'platform' => $item->platform->name,
                    'product' => $item->product->title,
                    'status' => 'success',
                    'old_price' => (float)$oldPrice,
                    'new_price' => (float)$newPrice,
                    'price_changed' => $oldPrice != $newPrice
                ];
            } catch (Exception $e) {
               $report[] = [
                    'platform' => $item->platform->name ?? 'Неизвестно',
                    'product' => $item->product->title ?? 'Неизвестно',
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'summary' => [
                'total_processed' => count($report),
                'timestamp' => now()->toIso8601String()
            ],
            'data' => $report
        ], 200);
    }
}
