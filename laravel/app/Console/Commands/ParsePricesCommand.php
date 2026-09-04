<?php

namespace App\Console\Commands;

use App\Models\PlatformProduct;
use App\Services\Parsers\PriceParserManager;
use Exception;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('prices:parse')]
#[Description('Command description')]
class ParsePricesCommand extends Command
{
    protected $signature = 'prices:parse';

    protected $description = 'Background parsing and price updates on all platforms';

    protected PriceParserManager $parserManager;

    public function __construct(PriceParserManager $parserManager)
    {
        parent::__construct();
        $this->parserManager = $parserManager;
    }

    public function handle(): int
    {
        $this->info('Start of the background price update...');

        $platformProducts = PlatformProduct::with(['product', 'platform'])->get();

        foreach ($platformProducts as $item) {
            try {
                $strategy = $this->parserManager->getStrategy($item);
                $parsedData = $strategy->parse($item);

                if (isset($parsedData['skipped']) && $parsedData['skipped'] === true) {
                    $this->comment("Platform [{$item->platform->name}]: There was already an update today. Skipped.");
                    continue;
                }

                $oldPrice = $item->current_price;
                $newPrice = $parsedData['current_price'];

                if ($oldPrice != $newPrice) {
                    $item->priceHistories()->create(['price' => $newPrice]);
                }

                $item->update([
                    'current_price' => $newPrice,
                    'old_price' => $parsedData['old_price']
                ]);

                $this->info("Platform [{$item->platform->name}]: The price was successfully updated from {$oldPrice} to {$newPrice}");

            } catch (Exception $e) {
                $this->error("Error on the platform [{$item->platform->name}]: " . $e->getMessage());
            }
        }

        $this->info('The background price update has been successfully completed!');
        return Command::SUCCESS;
    }
}
