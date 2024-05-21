<?php

namespace App\services\ExchangeRate;

use App\exceptions\CommissionCalculatorException;
use App\exceptions\HttpClientException;

interface ExchangeRateServiceInterface
{
    /**
     * Retrieves the exchange rates.
     *
     * @return array The exchange rates.
     * @throws HttpClientException If there is an error with the HTTP request.
     * @throws CommissionCalculatorException If the response is unexpected.
     */
    public function getExchangeRates(): array;
}
