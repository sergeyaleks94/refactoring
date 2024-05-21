<?php

namespace Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;
use App\services\BinList\BinListService;
use App\exceptions\HttpClientException;
use App\exceptions\CommissionCalculatorException;
use JsonException;

class BinListServiceTest extends TestCase
{
    /**
     * @throws HttpClientException
     * @throws Exception
     * @throws CommissionCalculatorException
     * @throws JsonException
     */
    public function testGetCountryCodeSuccess(): void
    {
        $bin = '45717360';
        $countryCode = 'DE';
        $responseBody = json_encode(['country' => ['alpha2' => $countryCode]], JSON_THROW_ON_ERROR);

        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')
            ->willReturn(new Response(200, [], $responseBody));

        $service = new BinListService($clientMock);
        $result = $service->getCountryCode($bin);

        $this->assertEquals($countryCode, $result);
    }

    /**
     * @throws Exception
     * @throws JsonException
     * @throws HttpClientException
     */
    public function testGetCountryCodeInvalidResponse(): void
    {
        $bin = '45717360';

        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')
            ->willReturn(new Response(200, [], json_encode(['country' => []], JSON_THROW_ON_ERROR)));

        $service = new BinListService($clientMock);

        $this->expectException(HttpClientException::class);
        $service->getCountryCode($bin);
    }

    /**
     * @throws Exception
     * @throws CommissionCalculatorException
     */
    public function testGetCountryCodeHttpClientException(): void
    {
        $bin = '45717360';

        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')
            ->willReturn(new Response(229));

        $service = new BinListService($clientMock);

        $this->expectException(HttpClientException::class);
        $service->getCountryCode($bin);
    }
}
