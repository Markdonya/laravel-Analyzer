<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CryptoPriceService
{
    private array $exchanges = [
        'binance' => [
            'url' => 'https://api.binance.com/api/v3/ticker/price',
            'symbol_param' => 'symbol'
        ],
        'bybit' => [
            'url' => 'https://api.bybit.com/v5/market/tickers',
            'symbol_param' => 'symbol',
            'category' => 'spot'
        ],
        'poloniex' => [
            'url' => 'https://api.poloniex.com/markets',
            'symbol_param' => 'symbol'
        ],
        'whitebit' => [
            'url' => 'https://whitebit.com/api/v4/public/ticker',
            'symbol_param' => 'market'
        ],
        'jbex' => [
            'url' => 'https://api.jbex.com/api/v1/ticker/price',
            'symbol_param' => 'symbol'
        ]
    ];

    public function getPricesFromAllExchanges(string $pair): array
    {
        $results = [];
        
        foreach ($this->exchanges as $exchange => $config) {
            try {
                $price = $this->getPriceFromExchange($exchange, $pair);
                if ($price !== null) {
                    $results[$exchange] = $price;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return $results;
    }

    public function getPriceComparison(string $pair): array
    {
        $prices = $this->getPricesFromAllExchanges($pair);
        
        if (empty($prices)) {
            return [];
        }
        
        $minPrice = min($prices);
        $maxPrice = max($prices);
        
        return [
            'pair' => $pair,
            'min_price' => $minPrice,
            'min_exchange' => array_search($minPrice, $prices),
            'max_price' => $maxPrice,
            'max_exchange' => array_search($maxPrice, $prices),
            'all_prices' => $prices
        ];
    }

    public function getArbitrageOpportunities(): array
    {
        $opportunities = [];
        $commonPairs = $this->getCommonPairs();
        
        foreach ($commonPairs as $pair) {
            $prices = $this->getPricesFromAllExchanges($pair);
            
            if (count($prices) < 2) {
                continue;
            }
            
            $minPrice = min($prices);
            $maxPrice = max($prices);
            $profitPercentage = (($maxPrice - $minPrice) / $minPrice) * 100;
            
            if ($profitPercentage > 0.1) {
                $opportunities[] = [
                    'pair' => $pair,
                    'min_price' => $minPrice,
                    'min_exchange' => array_search($minPrice, $prices),
                    'max_price' => $maxPrice,
                    'max_exchange' => array_search($maxPrice, $prices),
                    'profit_percentage' => round($profitPercentage, 2)
                ];
            }
        }
        
        usort($opportunities, function($a, $b) {
            return $b['profit_percentage'] <=> $a['profit_percentage'];
        });
        
        return array_slice($opportunities, 0, 20);
    }

    public function getPriceFromExchange(string $exchange, string $pair): ?float
    {
        switch ($exchange) {
            case 'binance':
                return $this->getBinancePrice($pair);
            case 'bybit':
                return $this->getBybitPrice($pair);
            case 'poloniex':
                return $this->getPoloniexPrice($pair);
            case 'whitebit':
                return $this->getWhitebitPrice($pair);
            case 'jbex':
                return $this->getJbexPrice($pair);
            default:
                return null;
        }
    }

    private function getBinancePrice(string $pair): ?float
    {
        $response = Http::withoutVerifying()->get('https://api.binance.com/api/v3/ticker/price', [
            'symbol' => str_replace('/', '', $pair)
        ]);
        
        if ($response->successful()) {
            return (float) $response->json('price');
        }
        
        return null;
    }

    private function getBybitPrice(string $pair): ?float
    {
        $response = Http::withoutVerifying()->get('https://api.bybit.com/v5/market/tickers', [
            'category' => 'spot',
            'symbol' => str_replace('/', '', $pair)
        ]);
        
        if ($response->successful()) {
            $data = $response->json('result.list');
            if (is_array($data) && !empty($data)) {
                return (float) $data[0]['lastPrice'];
            }
        }
        
        return null;
    }

    private function getPoloniexPrice(string $pair): ?float
    {
        $symbol = str_replace('/', '_', $pair);
        $response = Http::withoutVerifying()->get("https://api.poloniex.com/markets/{$symbol}/price");
        
        if ($response->successful()) {
            return (float) $response->json('price');
        }
        
        return null;
    }

    private function getWhitebitPrice(string $pair): ?float
    {
        $market = str_replace('/', '_', $pair);
        $response = Http::withoutVerifying()->get("https://whitebit.com/api/v4/public/ticker", [
            'market' => $market
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            if (is_array($data) && !empty($data)) {
                return (float) $data[0]['last_price'];
            }
        }
        
        return null;
    }

    private function getJbexPrice(string $pair): ?float
    {
        $symbol = str_replace('/', '', $pair);
        $response = Http::withoutVerifying()->get("https://api.jbex.com/api/v1/ticker/price", [
            'symbol' => $symbol
        ]);
        
        if ($response->successful()) {
            return (float) $response->json('price');
        }
        
        return null;
    }

    private function getCommonPairs(): array
    {
        return [
            'BTC/USDT',
            'ETH/USDT',
            'BNB/USDT',
            'ADA/USDT',
            'XRP/USDT',
            'SOL/USDT',
            'DOT/USDT',
            'DOGE/USDT',
            'AVAX/USDT',
            'MATIC/USDT',
            'LINK/USDT',
            'UNI/USDT',
            'LTC/USDT',
            'BCH/USDT',
            'FIL/USDT'
        ];
    }
}
