<?php

namespace App\Repositories;

use App\Contracts\MovieRepositoryInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class OmdbRepository implements MovieRepositoryInterface
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly string $apiKey,
        private readonly string $apiUrl,
    ) {
    }

    /**
     * Возвращает данные о фильме из OMDb API по IMDB ID.
     *
     * @param  string  $imdbId  IMDB идентификатор фильма
     * @return array|null данные о фильме или null если не найден
     *
     * @throws ClientExceptionInterface
     */
    #[\Override]
    public function findByImdbId(string $imdbId): ?array
    {
        $url = $this->apiUrl . '?apikey=' . $this->apiKey . '&i=' . $imdbId;

        $request = $this->requestFactory->createRequest('GET', $url);
        $response = $this->httpClient->sendRequest($request);

        return json_decode($response->getBody()->getContents(), true);
    }
}
