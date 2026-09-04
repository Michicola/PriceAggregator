<?php

namespace App\Services\Parsers;

use App\Models\PlatformProduct;
use Exception;
use Http;

class TOVStrategy implements ParserStrategyInterface
{
    public function parse(PlatformProduct $platformProduct): array
    {   
        if ($platformProduct->updated_at && $platformProduct->updated_at->isToday()) {
        return [
            'current_price' => (float)$platformProduct->current_price,
            'old_price' => $platformProduct->old_price ? (float)$platformProduct->old_price : null,
            'skipped' => true 
        ];
        }

        $url = $platformProduct->url;
        // 1. Download the HTML page of the product
        $response = Http::withoutVerifying()
            ->timeout(10)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9'
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new Exception("Couldn't load the page 21vek.by . Status code: {$response->status()}");
        }

        $html = $response->body();

        // 2. Cut out the price 
        preg_match('/"price":\s*"?([\d\.]+)"?/', $html, $priceMatches);
        preg_match('/"price_old":\s*"?([\d\.]+)"?/', $html, $oldPriceMatches);

        $currentPrice = isset($priceMatches[1]) ? (float)$priceMatches[1] : null;
        $oldPrice = isset($oldPriceMatches[1]) ? (float)$oldPriceMatches[1] : null;

        if (is_null($currentPrice)) {
            throw new Exception("Page 21vek was downloaded successfully, but the price tag could not be found");
        }

        // 3. Returning the data structure
        return [
            'current_price' => $currentPrice,
            'old_price' => $oldPrice
        ];
    }
}
