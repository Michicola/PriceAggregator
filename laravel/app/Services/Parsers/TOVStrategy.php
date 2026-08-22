<?php

namespace App\Services\Parsers;

class TOVStrategy implements ParserStrategyInterface
{
    public function parse(string $url): array
    {
         //Mock
        return [
            'current_price' => 54.00, 
            'old_price' => 57.12
        ];
    }
}
