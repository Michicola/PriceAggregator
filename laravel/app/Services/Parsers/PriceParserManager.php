<?php

namespace App\Services\Parsers;

use App\Models\PlatformProduct;
use InvalidArgumentException;

//The class that decides which strategy to pull out
class PriceParserManager
{
    protected array $strategies = [];

    public function registerStrategy(string $domainKey, ParserStrategyInterface $strategy): void
    {
        $this->strategies[$domainKey] = $strategy;
    }

    public function getStrategy(PlatformProduct $platformProduct): ParserStrategyInterface
    {
        foreach ($this->strategies as $domainKey => $strategy) {
            if (str_contains($platformProduct->url, $domainKey)) {
                return $strategy;
            }
        }

        throw new InvalidArgumentException("The parser for this store is not registered");
    }
}
