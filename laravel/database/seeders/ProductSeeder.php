<?php

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\Product;
use App\Models\PlatformProduct;
use App\Models\PriceHistory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $wb = Platform::create([
            'name'=>'Wildberries',
            'base_url'=>'https://www.wildberries.by/'
        ]);
        $ozon = Platform::create([
            'name'=>'Ozon',
            'base_url'=>'https://ozon.by/'
        ]);
        $twentyOneVek = Platform::create([
            'name'=>'21vek',
            'base_url'=>'https://www.21vek.by/'
        ]);
        
        $monge = Product::create([
            'title' => 'Сухой корм для стерилизованных кошек с уткой, 1.5кг',
            'barcode'=> '123123123'
        ]);
        
        $mongeWB = $monge->platformProducts()->create([
            'platform_id' => $wb->id,
            'url' => 'https://www.wildberries.by/catalog/586364926/detail.aspx',
            'current_price' => 58.23,
            'old_price' => 130.26
        ]);

        $mongeWB->priceHistories()->createMany([
            ['price'=> 65.79, 'created_at'=>now()->subDays(2)],
            ['price'=> 130.26, 'created_at'=>now()->subDays(1)],
            ['price'=> 58.23, 'created_at'=>now()],
        ]);

        $mongeOzon = $monge->platformProducts()->create([
            'platform_id' => $ozon->id,
            'url' => 'https://ozon.by/product/monge-monoprotein-sterilised-suhoy-korm-dlya-sterilizovannyh-koshek-s-utkoy-1-5-kg-2894103338/?at=1kWj1-SVdEpAdot9xIAst25VnqiP50Pd&sh=QQYMlIsCFg',
            'current_price' => 75.51,
            'old_price' => 80.69
        ]);

        $mongeOzon->priceHistories()->create(['price'=>75.51]);

        $monge21 = $monge->platformProducts()->create([
            'platform_id' => $twentyOneVek->id,
            'url' => 'https://www.21vek.by/cat_food/monoproteinsterilisedduck_monge_5999652.html',
            'current_price' => 55.50,
            'old_price' => 57.12
        ]);

        $monge21->priceHistories()->createMany([
            ['price'=> 60.35, 'created_at'=>now()->subDays(2)],
            ['price'=> 57.12, 'created_at'=>now()->subDays(1)],
            ['price'=> 55.50, 'created_at'=>now()],
        ]);
        
        
    }
}
