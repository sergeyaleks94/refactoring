<?php

namespace App;

use App\exceptions\CommissionCalculatorException;
use App\services\BinList\BinListServiceInterface;
use App\services\ExchangeRate\ExchangeRateServiceInterface;
use Exception;
use JsonException;

class CommissionCalculator
{
    private const EUR = 'EUR';
    private const EUR_COMMISSION = 0.01;
    private const NOT_EUR_COMMISSION = 0.02;

    private const EU_COUNTRY_CODES = [
        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DE',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GR',
        'HR',
        'HU',
        'IE',
        'IT',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'PL',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK'
    ];

    private array $rates;
    private BinListServiceInterface $binListService;
    private ExchangeRateServiceInterface $exchangeRateService;

    public function __construct(
        BinListServiceInterface $binListService,
        ExchangeRateServiceInterface $exchangeRateService
    ) {
        $this->binListService = $binListService;
        $this->exchangeRateService = $exchangeRateService;
    }

    /**
     * @throws Exception
     */
    public function calculate(string $filePath): array
    {
        $result = [];
        $rows = $this->fetchRows($filePath);
        $this->rates = $this->exchangeRateService->getExchangeRates();

        foreach ($rows as $row) {
            if (empty($row)) {
                // I don't know what to do with an empty row, but it can be end of file or just a tab mistake
                continue;
            }

            try {
                $result[] = $this->calculateRow($row);
            } catch (CommissionCalculatorException $e) {
                // Log or handle the exception if needed, but continue processing other rows
                throw new CommissionCalculatorException($e->getMessage());
            }
        }

        return $result;
    }

    /**
     * @param string $row
     *
     * @return float
     * @throws Exception
     *
     */
    private function calculateRow(string $row): float
    {
        $requiredKeys = ['bin', 'amount', 'currency'];

        $values = $this->extractValues($row);

        if (array_diff($requiredKeys, array_keys($values))) {
            throw new CommissionCalculatorException('Row not contain necessary keys: ' . $row);
        }

        ['bin' => $bin, 'amount' => $amount, 'currency' => $currency] = $values;

        $countryCode = $this->binListService->getCountryCode($bin);
        $rate = $this->rates[$currency] ?? 0;

        if ($currency !== self::EUR && $rate > 0) {
            $amount /= $rate;
        }

        $commissionRate = $this->isEuropeanCountry($countryCode) ? self::EUR_COMMISSION : self::NOT_EUR_COMMISSION;
        $amount *= $commissionRate;

        return round($amount, 2);
    }

    /**
     * @throws CommissionCalculatorException
     */
    private function fetchRows(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new CommissionCalculatorException('Requested file not exists..');
        }

        if (!($fileContent = file_get_contents($filePath))) {
            throw new CommissionCalculatorException('Cannot get content from file.');
        }

        $rows = preg_split('/\r\n|\r|\n/', $fileContent);

        if (empty($rows)) {
            throw new CommissionCalculatorException('File content is empty.');
        }

        return $rows;
    }

    /**
     * @param string $row
     *
     * @return array
     *
     * @throws JsonException
     */
    private function extractValues(string $row): array
    {
        return json_decode($row, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $countryCode
     *
     * @return bool
     */
    private function isEuropeanCountry(string $countryCode): bool
    {
        return in_array($countryCode, self::EU_COUNTRY_CODES, true);
    }
}
