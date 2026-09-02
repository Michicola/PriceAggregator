<?php

namespace App\Services\Parsers;

use App\Models\PlatformProduct;
use Exception;

class WBStrategy implements ParserStrategyInterface
{
    public function parse(PlatformProduct $platformProduct): array
    {
        // 1. Check whether the record itself has been updated in the database TODAY
        if ($platformProduct->updated_at && $platformProduct->updated_at->isToday()) {
            
            // If there is only 1 entry in the history (just from the sider), allow it to run once
            if ($platformProduct->priceHistories()->count() > 1) {
                return [
                    'current_price' => (float)$platformProduct->current_price,
                    'old_price' => (float)$platformProduct->old_price,
                    'skipped' => true //The limit has been reached today
                ];
            }
        }

        // 2. Take the old price from db
        $oldPrice = $platformProduct->old_price ? (float)$platformProduct->old_price : 130.26;
        $previousPrice = (float)$platformProduct->current_price;

        // 3. Count the number of entries in the history of this particular product on the WB
        $historyCount = $platformProduct->priceHistories()->count();

        // If there are 3, 6, 9 entries... the price changes
        if ($historyCount > 0 && $historyCount % 3 === 0) {
            $step = rand(100, 1000) / 100; 
            $currentPrice = $previousPrice + $step;
            
            if ($currentPrice >= $oldPrice) {
                $currentPrice = $oldPrice - 5.00;
            }
        } else {
            $step = rand(50, 200) / 100; 
            $currentPrice = $previousPrice - $step;

            if ($currentPrice < 10.00) {
                $currentPrice = 10.00; 
            }
        }

        return [
            'current_price' => round($currentPrice, 2),
            'old_price' => $oldPrice,
            'skipped' => false
        ];
    }
}