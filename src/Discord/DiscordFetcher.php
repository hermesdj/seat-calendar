<?php

namespace Seat\Kassie\Calendar\Discord;

use Composer\InstalledVersions;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;

class DiscordFetcher
{
    private readonly Client $client;

    public function __construct(string $base_uri, string $token)
    {
        try {
            $version = InstalledVersions::getPrettyVersion('hermesdj/seat-calendar');
        } catch (OutOfBoundsException) {
            $version = 'dev';
        }

        $stack = HandlerStack::create();
        $stack->push(new RateLimiterMiddleware);

        $this->client = new Client([
            'base_uri' => $base_uri,
            'headers' => [
                'Authorization' => sprintf('Bot %s', $token),
                'Content-Type' => 'application/json',
                'User-Agent' => sprintf('hermesdj@seat-calendar/%s GitHub SeAT', $version),
            ],
            'handler' => $stack,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function request(string $method, string $uri = '', array $options = []): ResponseInterface
    {
        return $this->client->request($method, $uri, $options);
    }
}
