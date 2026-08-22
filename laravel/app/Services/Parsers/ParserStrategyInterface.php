<?php

namespace App\Services\Parsers;

interface ParserStrategyInterface
{
    //The store's link is accepted as input, and an array of prices is returned.
    public function parse(string $url): array;
}