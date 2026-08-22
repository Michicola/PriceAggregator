<?php

namespace Tests\Feature;

use App\Models\Platform;
use App\Models\Product;
use App\Services\Parsers\PriceParserManager;
use Illuminate\Foundation\Testing\RefreshDatabase; 
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PriceParserTest extends TestCase
{
    use RefreshDatabase;

    public function test_parser_strategy_updates_prices_and_records_history(): void
    {
        $wb = Platform::create([
            'name' => 'Wildberries',
            'base_url' => 'https://wildberries.by'
        ]);
    
         $product = Product::create([
            'title' => 'Тестовый корм Monge',
            'barcode' => '999999999'
        ]);

        $platformProduct = $product->platformProducts()->create([
            'platform_id' => $wb->id,
            'url' => 'https://wildberries.bycatalog/12345/detail.aspx',
            'current_price' => 100.00,
            'old_price' => 150.00
        ]);

        $manager = app(PriceParserManager::class);
        $strategy = $manager->getStrategy($platformProduct->url);
        $parsedData = $strategy->parse($platformProduct->url); 

        $platformProduct->update([
            'current_price' => $parsedData['current_price'],
            'old_price' => $parsedData['old_price'],
        ]);

        $platformProduct->priceHistories()->create([
            'price' => $parsedData['current_price']
        ]);

        $this->assertDatabaseHas('platform_products', [
            'id' => $platformProduct->id,
            'current_price' => 55.40,
        ]);

        $this->assertDatabaseHas('price_histories', [
            'platform_product_id' => $platformProduct->id,
            'price' => 55.40,
        ]);
        }
}
