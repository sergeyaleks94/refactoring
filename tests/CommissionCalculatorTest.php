<?php

namespace Tests;

use App\exceptions\CommissionCalculatorException;
use App\services\BinList\BinListService;
use App\services\ExchangeRate\ExchangeRateService;
use Exception;
use ReflectionException;
use PHPUnit\Framework\TestCase;
use App\CommissionCalculator;
use ReflectionClass;
use PHPUnit\Framework\MockObject\Exception AS MockException;

class CommissionCalculatorTest extends TestCase
{
    private CommissionCalculator $commissionCalculator;

    /**
     * @throws MockException
     */
    protected function setUp(): void
    {
        $binListService = $this->createMock(BinListService::class);
        $binListService->method('getCountryCode')
            ->willReturn('UK');
        $exchangeRateService = $this->createMock(ExchangeRateService::class);
        $exchangeRateService->method('getExchangeRates')
            ->willReturn([
                'EUR' => 1,
                'USD' => 0.8332
            ]);

        $this->commissionCalculator = new CommissionCalculator($binListService, $exchangeRateService);
    }

    /**
     * @throws Exception|MockException
     */
    public function testCalculate(): void
    {
        $binListService = $this->createMock(BinListService::class);
        $binListService->method('getCountryCode')
            ->willReturn('UK');
        $exchangeRateService = $this->createMock(ExchangeRateService::class);
        $exchangeRateService->method('getExchangeRates')
            ->willReturn([
                'EUR' => 1,
                'USD' => 0.8332
            ]);

        $filePath = 'testfile.txt';
        $rows = [
            '{"bin":"45717360","amount":"100.00","currency":"EUR"}',
            '{"bin":"516793","amount":"50.00","currency":"USD"}'
        ];

        file_put_contents($filePath, implode(PHP_EOL, $rows));
        $commissionCalculator = new CommissionCalculator($binListService, $exchangeRateService);

        $result = $commissionCalculator->calculate($filePath);

        $this->assertEquals([2, 1.2], $result);

        unlink($filePath);
    }

    /**
     * @throws Exception
     */
    public function testFetchRowsThrowsExceptionForMissingFile(): void
    {
        $this->expectException(CommissionCalculatorException::class);
        $this->expectExceptionMessage('Requested file not exists.');

        $this->commissionCalculator->calculate('nonexistentfile.txt');
    }

    /**
     * @throws ReflectionException
     */
    public function testCalculateRowThrowsExceptionForMissingKeys(): void
    {
        $row = '{"amount":"100.00","currency":"EUR"}'; // Missing 'bin'

        $this->expectException(CommissionCalculatorException::class);
        $this->expectExceptionMessage('Row not contain necessary keys: ' . $row);

        $reflection = new ReflectionClass($this->commissionCalculator);
        $method = $reflection->getMethod('calculateRow');
        $method->setAccessible(true);

        $method->invokeArgs($this->commissionCalculator, [$row]);
    }
}
