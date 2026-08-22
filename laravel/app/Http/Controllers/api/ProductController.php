<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
     public function index(): JsonResponse
    {
        $products = Product::with([
            'platformProducts.platform', 
            'platformProducts.priceHistories'
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }
}
