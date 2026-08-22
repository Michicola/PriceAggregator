<?php

namespace App\Services\Parsers;

class WBStrategy implements ParserStrategyInterface
{
    public function parse(string $url): array
    {
        //Mock
        return [
            'current_price' => 55.40, 
            'old_price' => 130.26
        ];
    }
}
