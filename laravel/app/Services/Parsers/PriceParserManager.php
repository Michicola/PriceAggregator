<?php

namespace App\Services\Parsers;

use InvalidArgumentException;

//The class that decides which strategy to pull out
class PriceParserManager
{
    protected array $strategies = [];

     public function registerStrategy(string $domainKey, ParserStrategyInterface $strategy): void
    {
        $this->strategies[$domainKey] = $strategy;
    }

       public function getStrategy(string $url): ParserStrategyInterface
    {
        foreach ($this->strategies as $domainKey => $strategy) {
            if (str_contains($url, $domainKey)) {
                return $strategy;
            }
        }

        throw new InvalidArgumentException("Парсер для данного магазина не зарегистрирован.");
    }
}
