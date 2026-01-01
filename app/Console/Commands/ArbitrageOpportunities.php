<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CryptoPriceService;

class ArbitrageOpportunities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:arbitrage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find arbitrage opportunities between exchanges';

    /**
     * Execute the console command.
     */
    public function handle(CryptoPriceService $service)
    {
        $this->info('Searching for arbitrage opportunities...');
        
        $opportunities = $service->getArbitrageOpportunities();
        
        if (empty($opportunities)) {
            $this->info('No arbitrage opportunities found');
            return 0;
        }
        
        $this->table(
            ['Pair', 'Buy From', 'Buy Price', 'Sell To', 'Sell Price', 'Profit %'],
            array_map(function($opp) {
                return [
                    $opp['pair'],
                    $opp['min_exchange'],
                    $opp['min_price'],
                    $opp['max_exchange'],
                    $opp['max_price'],
                    $opp['profit_percentage'] . '%'
                ];
            }, $opportunities)
        );
        
        return 0;
    }
}
