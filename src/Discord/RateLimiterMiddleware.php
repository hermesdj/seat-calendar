<?php

namespace Seat\Kassie\Calendar\Discord;

use Closure;
use Illuminate\Support\Facades\Redis;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class RateLimiterMiddleware
{
    final public const MAP_ENDPOINTS = [
        '/guilds/{guild.id}/scheduled-events' => '/\/api\/guilds\/[0-9]+\/scheduled-events/i',
        '/guilds/{guild.id}/scheduled-events/{guild_scheduled_event.id}' => '/\/api\/guilds\/[0-9]+\/scheduled-events\/[0-9]+/i',
        '/guilds/{guild.id}/scheduled-events/{guild_scheduled_event.id}/users' => '/\/api\/guilds\/[0-9]+\/scheduled-events\/[0-9]+\/users/i',
    ];

    final public const REDIS_CACHE_PREFIX = 'seat:seat-calendar.drivers.discord';

    public function __invoke(callable $handler): Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            // determine request timestamp
            $now = time();

            // retrieve throttler metadata for requested endpoint
            $key = $this->getCacheKey($request->getUri());
            $metadata = Redis::get($key) ?: null;

            if (! is_null($metadata)) {
                $metadata = unserialize($metadata);

                // compute delay between reset time and current time
                // add 10 seconds to the result in order to avoid server clock issues
                // furthermore, the limit is removed after the exact time
                $delay = $metadata->reset + 10 - $now;

                // in case limit is near to be reached, we pause the request for computed duration
                if ($metadata->remaining < 2 && $delay > 0) {
                    sleep($delay);
                }
            }

            // send the request and retrieve response
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) use ($key): ResponseInterface {

                // update cache entry for the endpoint using new RateLimit / RateReset values
                $metadata = $this->getEndpointMetadata($response);
                Redis::setex($key, 60 * 60 * 24 * 7, serialize($metadata));

                // forward response to the stack
                return $response;
            });
        };
    }

    private function getCacheKey(UriInterface $uri): string
    {
        $match_pattern = $uri->getPath();

        // attempt to resolve the requested endpoint
        foreach (self::MAP_ENDPOINTS as $endpoint => $pattern) {
            if (preg_match($pattern, $uri->getPath()) === 1) {
                $match_pattern = $endpoint;
            }
        }

        // generate a hash based on the endpoint
        $hash = sha1($match_pattern);

        // return a cache key built using prefix, hash and requested type
        return sprintf('%s.%s.metadata', self::REDIS_CACHE_PREFIX, $hash);
    }

    private function getEndpointMetadata(ResponseInterface $response): object
    {
        $remaining = (int) $response->getHeaderLine('X-RateLimit-Remaining') ?: 0;
        $reset = (int) $response->getHeaderLine('X-RateLimit-Reset') ?: 0;

        return (object) [
            'reset' => $reset,
            'remaining' => $remaining,
        ];
    }
}
