<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CryptoPriceService;

class CompareCryptoPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:compare {pair}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare cryptocurrency prices across exchanges';

    /**
     * Execute the console command.
     */
    public function handle(CryptoPriceService $service)
    {
        $pair = strtoupper($this->argument('pair'));
        
        if (!str_contains($pair, '/')) {
            $this->error('Invalid pair format. Use format like BTC/USDT');
            return 1;
        }
        
        $this->info("Comparing prices for {$pair}...");
        
        $comparison = $service->getPriceComparison($pair);
        
        if (empty($comparison)) {
            $this->error('No price data available for this pair');
            return 1;
        }
        
        $this->info("Lowest price: {$comparison['min_price']} on {$comparison['min_exchange']}");
        $this->info("Highest price: {$comparison['max_price']} on {$comparison['max_exchange']}");
        
        $this->table(['Exchange', 'Price'], array_map(function($exchange, $price) {
            return [$exchange, $price];
        }, array_keys($comparison['all_prices']), $comparison['all_prices']));
        
        return 0;
    }
}
