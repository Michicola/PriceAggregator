<?php

namespace Tests\Feature;

use App\Models\Platform;
use App\Models\PlatformProduct;
use App\Models\Product;
use App\Services\Parsers\PriceParserManager;
use Illuminate\Foundation\Testing\RefreshDatabase; 
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

        //To bypass the "once a day" block in the test, we force yesterday's date
        $platformProduct = new PlatformProduct();
        $platformProduct->timestamps = false;
        $platformProduct->fill([
            'product_id' => $product->id,
            'platform_id' => $wb->id,
            'url' => 'https://wildberries.by',
            'current_price' => 100.00,
            'old_price' => 150.00,
            'updated_at' => now()->subDay(), 
            'created_at' => now()->subDay()
        ]);
        $platformProduct->save();
        $platformProduct->priceHistories()->create(['price' => 100.00]);

        $manager = app(PriceParserManager::class);

        $strategy = $manager->getStrategy($platformProduct);
        $parsedData = $strategy->parse($platformProduct); 

        $platformProduct->timestamps = true;

        $platformProduct->update([
            'current_price' => $parsedData['current_price'],
            'old_price' => $parsedData['old_price'],
        ]);

        $platformProduct->priceHistories()->create([
            'price' => $parsedData['current_price']
        ]);

        $this->assertDatabaseHas('platform_products', [
            'id' => $platformProduct->id,
        ]);
        $this->assertLessThan(100.00, $platformProduct->fresh()->current_price);

        //Check: a new entry with changed price has appeared in the price history
        $this->assertDatabaseHas('price_histories', [
            'platform_product_id' => $platformProduct->id,
            'price' => $parsedData['current_price'],
        ]);
    }
}