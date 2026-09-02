<?php

namespace Tests\Feature; 

use App\Models\Platform;
use App\Models\PlatformProduct;
use App\Models\Product;
use App\Services\Parsers\WBStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WBStrategyTest extends TestCase
{
    use RefreshDatabase;
     private function createTestProduct(array $attributes = []): PlatformProduct
    {
        $product = Product::create([
            'title' => 'Тестовый корм WB',
            'barcode' => '8009470011952'
        ]);

        $platform = Platform::create([
            'name' => 'Wildberries',
            'base_url' => 'https://wildberries.by'
        ]);

        $platformProduct = new PlatformProduct();
        $platformProduct->timestamps = false;

        $data = array_merge([
            'product_id' => $product->id,
            'platform_id' => $platform->id,
            'url' => 'https://wildberries.bycatalog/123/detail.aspx',
            'current_price' => 60.00,
            'old_price' => 130.00,
            'updated_at' => now()->subDay(), 
            'created_at' => now()->subDay()
        ], $attributes);

        $platformProduct->fill($data);
        $platformProduct->save();

        return $platformProduct;
    }

    /**
     * Test 1: check the price is going DOWN in the usual steps (1st entry in the history)
     */
    public function test_wb_strategy_decreases_price_on_regular_steps(): void
    {
        $platformProduct = $this->createTestProduct([
            'current_price' => 60.00,
            'old_price' => 130.00
        ]);

        $platformProduct->priceHistories()->create(['price' => 60.00]);

        $strategy = new WBStrategy();
        $result = $strategy->parse($platformProduct);

        $this->assertLessThan(60.00, $result['current_price']);
        $this->assertEquals(130.00, $result['old_price']);
        $this->assertFalse($result['skipped'] ?? true);
    }

    /**
     * Test 2: check the algorithm "Every third time HIGHER" (3 entries in the history)
     */
    public function test_wb_strategy_increases_price_on_every_third_step(): void
    {
        $platformProduct = $this->createTestProduct([
            'current_price' => 50.00,
            'old_price' => 130.00
        ]);

        $platformProduct->priceHistories()->createMany([
            ['price' => 55.00],
            ['price' => 52.00],
            ['price' => 50.00],
        ]);

        $strategy = new WBStrategy();
        $result = $strategy->parse($platformProduct);

        $this->assertGreaterThan(50.00, $result['current_price']);
        $this->assertEquals(130.00, $result['old_price']);
        $this->assertFalse($result['skipped'] ?? true);
    }

    /**
     * Test 3: check the blocking of a repeat request on the same day (AI)
     */
    public function test_wb_strategy_skips_update_if_already_updated_today(): void
    {
    $product = Product::create([
        'title' => 'Тестовый корм WB',
        'barcode' => '8009470011952'
    ]);

    $platform = Platform::create([
        'name' => 'Wildberries',
        'base_url' => 'https://wildberries.by'
    ]);

    $platformProduct = PlatformProduct::create([
        'product_id' => $product->id,
        'platform_id' => $platform->id,
        'url' => 'https://wildberries.bycatalog/123/detail.aspx',
        'current_price' => 58.23,
        'old_price' => 130.26
    ]);

    $platformProduct->priceHistories()->create(['price' => 65.00]);
    $platformProduct->priceHistories()->create(['price' => 60.00]);
    $platformProduct->priceHistories()->create(['price' => 58.23]);

    $platformProduct->refresh();
   
    $strategy = new WBStrategy();
   
    $result = $strategy->parse($platformProduct);

    $this->assertTrue($result['skipped'] ?? false);
    $this->assertEquals(58.23, $result['current_price']);
}
}