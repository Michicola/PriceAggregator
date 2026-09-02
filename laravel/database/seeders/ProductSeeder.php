<?php

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $wb = Platform::create([
            'name'=>'Wildberries',
            'base_url'=>'https://www.wildberries.by/'
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
            'article_id' => '2415278',
            'current_price' => 58.23,
            'old_price' => 130.26
        ]);

        $mongeWB->priceHistories()->createMany([
            ['price'=> 65.79, 'created_at'=>now()->subDays(2)],
            ['price'=> 130.26, 'created_at'=>now()->subDays(1)],
            ['price'=> 58.23, 'created_at'=>now()],
        ]);

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
