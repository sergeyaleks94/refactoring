<?php

namespace App\services\BinList;

use App\exceptions\HttpClientException;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class BinListService implements BinListServiceInterface
{
    private const BASE_URL = 'https://lookup.binlist.net/';

    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws HttpClientException|GuzzleException
     */
    public function getCountryCode(string $bin): string
    {
        try {
            $response = $this->client->get(self::BASE_URL . $bin);

            $this->validateResponse($response);

            $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if (!isset($result['country']['alpha2'])) {
                throw new HttpClientException('Unexpected response from BinList API');
            }

            return $result['country']['alpha2'];
        } catch (Exception $e) {
            throw new HttpClientException('Failed to request data from BinList', 0, $e);
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws HttpClientException
     */
    private function validateResponse(ResponseInterface $response): void
    {
        if ($response->getStatusCode() !== 200) {
            throw new HttpClientException('Failed to request data from BinList');
        }
    }
}
