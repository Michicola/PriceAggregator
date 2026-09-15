<?php

namespace App\Services\Parsers;

use App\Models\PlatformProduct;
use Exception;
use Http;
use Symfony\Component\DomCrawler\Crawler;

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

         // 2. Turning html into a tag tree
        $crawler = new Crawler($html);

        $currentPrice = null;
        $oldPrice = null;

        $crawler->filter('script[type="application/ld+json"]')
            ->each(function (Crawler $node) use (&$currentPrice, &$oldPrice) {
            if (!is_null($currentPrice)) {
                return;
            }

            $jsonData = json_decode($node->text(), true);

            //Checks that the text has turned into an array without errors
            //Checks if the word price is in the array
            if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['price'])) {
                //3. Assigning values to variables
                $currentPrice = (float)$jsonData['price'];
                $oldPrice = isset($jsonData['price_old']) ? (float)$jsonData['price_old'] : null;
            }
        });

        if (is_null($currentPrice)) {
            throw new Exception("Page 21vek was downloaded successfully, but the price tag could not be found");
        }

        // 4. Returning the data structure
        return [
            'current_price' => $currentPrice,
            'old_price' => $oldPrice
        ];
    }
}
