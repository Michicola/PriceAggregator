<?php

namespace Tests\Feature; 

use App\Models\Platform;
use App\Models\PlatformProduct;
use App\Models\Product;
use App\Services\Parsers\TOVStrategy;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TOVStrategyTest extends TestCase
{
    use RefreshDatabase; 
    public function test_tov_strategy_correctly_parses_html_prices(): void
    {
          Http::fake([
            '*' => Http::response('
                <html>
                    <body>
                        <script type="application/ld+json">
                            {
                                "price": "55.50",
                                "price_old": "57.12"
                            }
                        </script>
                    </body>
                </html>
            ', 200)
        ]);

        $product = Product::create([
            'title' => 'Тестовый корм Monge',
            'barcode' => '8009470011952'
        ]);

        $platform = Platform::create([
            'name' => '21vek',
            'base_url' => 'https://21vek.by'
        ]);

        $this->travelTo(now()->subDay());

        $platformProduct = PlatformProduct::create([
            'product_id' => $product->id,
            'platform_id' => $platform->id,
            'url' => 'https://21vek.bycat_food/monge.html',
            'current_price' => 50.00,
            'old_price' => null
        ]);

        $this->travelBack();

        $strategy = new TOVStrategy();
        $result = $strategy->parse($platformProduct);

        $this->assertEquals(55.50, $result['current_price']);
        $this->assertEquals(57.12, $result['old_price']);
    }
}