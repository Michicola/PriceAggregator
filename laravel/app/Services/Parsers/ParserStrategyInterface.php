<?php

namespace App\Services\Parsers;

use App\Models\PlatformProduct;

interface ParserStrategyInterface
{
    public function parse(PlatformProduct $platformProduct): array;
}