 Crypto Price Analyzer

Це проста система для анализа цiн на криптовалюти на рiзних биржах. 

Що може

- Получае цiни с 5 бирж: Binance, Bybit, Poloniex, Whitebit, Jbex
- Покаже низкую и високу цену для обраноi пари
- Шука возможности для арбiтража 
- Працюе через консольнi команди

 Установка

Потрибен PHP и Composer:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

 Как використовувати

 Сравнити цiни для пари

```bash
php artisan crypto:compare BTC/USDT
```

Покаже где сама низкая и висока цiна.

 Знайти арбитраж

```bash
php artisan crypto:arbitrage
```

Покаже пари де можно зарабити на разнице цен.

 Какие пари поддержуэ:

BTC/USDT, ETH/USDT, BNB/USDT, ADA/USDT, XRP/USDT, SOL/USDT, DOT/USDT, DOGE/USDT, AVAX/USDT, MATIC/USDT, LINK/USDT, UNI/USDT, LTC/USDT, BCH/USDT, FIL/USDT

Яу це працюе:

Е сервис `CryptoPriceService` котрий ходить на API бирж и получае цiни. Потом команди показують результати в удобном виде.

Якщо какой-то API не работае - система просто пропускае эту биржу и продолжае работать.

 Вимоги:

PHP 8.2, Laravel 12, HTTP клиент для запитiв

 Приклад роботи

```
php artisan crypto:compare BTC/USDT
Comparing prices for BTC/USDT...
Lowest price: 88146.74 on poloniex
Highest price: 88168.4 on bybit

```

Для арбитражу:


php artisan crypto:arbitrage
Searching for arbitrage opportunities...



