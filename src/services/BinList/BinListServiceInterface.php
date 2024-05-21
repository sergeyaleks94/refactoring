<?php

namespace App\services\BinList;

use App\exceptions\CommissionCalculatorException;
use App\exceptions\HttpClientException;

interface BinListServiceInterface
{
    /**
     * Retrieves the country code for a given BIN.
     *
     * @param string $bin The BIN number.
     * @return string The country code.
     * @throws CommissionCalculatorException If the response is unexpected.
     * @throws HttpClientException If there is an error with the HTTP request.
     */
    public function getCountryCode(string $bin): string;
}
