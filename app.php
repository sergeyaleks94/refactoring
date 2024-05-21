<?php

require 'vendor/autoload.php';

use App\CommissionCalculator;
use GuzzleHttp\Client;
use App\services\BinList\BinListService;
use App\services\ExchangeRate\ExchangeRateService;

//api key now required by ExchangeRateApi
$apiKey = '';

try {
    $httpClient = new Client();
    $binListService = new BinListService($httpClient);
    $exchangeRateService = new ExchangeRateService($httpClient, $apiKey);

    $commissionCalculator = new CommissionCalculator($binListService, $exchangeRateService);

    $result = $commissionCalculator->calculate($argv[1]);

    foreach ($result as $item) {
        echo $item . PHP_EOL;
    }
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
