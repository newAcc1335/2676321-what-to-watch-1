<?php

namespace App\Repositories;

use App\Contracts\MovieRepositoryInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class OmdbRepository implements MovieRepositoryInterface
{
    private const API_URL = 'https://www.omdbapi.com/';

    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private string $apiKey,
    ) {}

    public function findByImdbId(string $imdbId): ?array
    {
        $url = self::API_URL.'?apikey='.$this->apiKey.'&i='.$imdbId;

        $request = $this->requestFactory->createRequest('GET', $url);
        $response = $this->httpClient->sendRequest($request);

        return json_decode($response->getBody()->getContents(), true);
    }
}
