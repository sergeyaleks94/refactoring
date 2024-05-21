<?php

namespace Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use App\services\ExchangeRate\ExchangeRateService;
use App\exceptions\HttpClientException;
use App\exceptions\CommissionCalculatorException;
use JsonException;

class ExchangeRateServiceTest extends TestCase
{
    /**
     * @throws HttpClientException
     * @throws Exception
     * @throws CommissionCalculatorException
     * @throws JsonException
     */
    public function testGetExchangeRatesSuccess(): void
    {
        $apiKey = 'test_api_key';
        $rates = ['USD' => 1.2, 'EUR' => 1];
        $responseBody = json_encode(['rates' => $rates], JSON_THROW_ON_ERROR);

        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')
            ->willReturn(new Response(200, [], $responseBody));

        $service = new ExchangeRateService($clientMock, $apiKey);
        $result = $service->getExchangeRates();

        $this->assertEquals($rates, $result);
    }

    /**
     * @throws HttpClientException
     * @throws Exception|JsonException
     */
    public function testGetExchangeRatesInvalidResponse(): void
    {
        $apiKey = 'test_api_key';

        $clientMock = $this->createMock(Client::class);
        $clientMock->method('request')
            ->willReturn(new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)));

        $service = new ExchangeRateService($clientMock, $apiKey);

        $this->expectException(HttpClientException::class);
        $service->getExchangeRates();
    }
}
