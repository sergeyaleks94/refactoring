<?php

namespace App\services\ExchangeRate;

use App\exceptions\HttpClientException;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class ExchangeRateService implements ExchangeRateServiceInterface
{
    private const BASE_URL = 'api.exchangeratesapi.io/latest';

    private ClientInterface $client;
    private string $apiKey;

    public function __construct(ClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    /**
     * @return array
     *
     * @throws HttpClientException|GuzzleException
     */
    public function getExchangeRates(): array
    {
        try {
            $response = $this->client->get(self::BASE_URL, [
                'query' => ['access_key' => $this->apiKey]
            ]);

            $this->validateResponse($response);
            $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if (!isset($result['rates'])) {
                throw new HttpClientException('Unexpected response from ExchangeRate API');
            }

            return $result['rates'];
        } catch (Exception $e) {
            throw new HttpClientException('Failed to request data from ExchangeRate', 0, $e);
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws HttpClientException
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if ($response->getStatusCode() !== 200) {
            throw new HttpClientException('Failed to request data from ExchangeRate');
        }
    }
}
