<?php

namespace App\Services\Parsers;

use Override;

class OzonStrategy implements ParserStrategyInterface{
    public function parse(string $url): array
    {
         return [
            'current_price' => 74.90, 
            'old_price' => 80.69
        ];
    }
}