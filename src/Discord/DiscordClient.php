<?php

namespace Seat\Kassie\Calendar\Discord;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JsonException;
use Seat\Services\Exceptions\SettingException;

class DiscordClient
{
    final public const BASE_URI = 'https://discord.com/api';

    final public const VERSION = 'v6';

    private static ?DiscordClient $instance = null;

    private readonly object $client;

    private readonly string $guild_id;

    private string $bot_token;

    private string $owner_id;

    private function __construct(array $parameters)
    {
        $this->guild_id = $parameters['discord_guild_id'];
        $this->bot_token = $parameters['discord_bot_token'];
        $this->owner_id = $parameters['discord_owner_id'];

        $fetcher = DiscordFetcher::class;

        $base_uri = sprintf('%s/%s', rtrim(self::BASE_URI, '/'), self::VERSION);

        $this->client = new $fetcher($base_uri, $this->bot_token);
    }

    /**
     * @throws DiscordSettingsException
     * @throws SettingException
     */
    public static function getInstance(): DiscordClient
    {
        if (!isset(self::$instance)) {
            $guildId = setting('kassie.calendar.discord_guild_id', true);

            if (is_null($guildId)) {
                throw new DiscordSettingsException('Parameter guild_id is missing.');
            }

            $botToken = setting('kassie.calendar.discord_bot_token', true);

            if (is_null($botToken)) {
                throw new DiscordSettingsException('Parameter bot_token is missing.');
            }

            $ownerId = setting('kassie.calendar.discord_owner_id', true);

            self::$instance = new DiscordClient([
                'discord_guild_id' => $guildId,
                'discord_bot_token' => $botToken,
                'discord_owner_id' => $ownerId ?: null,
            ]);
        }

        return self::$instance;
    }

    public static function tearDown(): void
    {
        self::$instance = null;
    }

    /**
     * @throws GuzzleException|\JsonException
     */
    public function sendCall(string $method, string $endpoint, array $arguments = [])
    {
        $uri = ltrim($endpoint, '/');

        foreach ($arguments as $uri_parameter => $value) {
            if (!str_contains($uri, sprintf('{%s}', $uri_parameter))) {
                continue;
            }

            $uri = str_replace(sprintf('{%s}', $uri_parameter), $value, $uri);
            Arr::pull($arguments, $uri_parameter);
        }

        if ($method == 'GET') {
            $response = $this->client->request($method, $uri, [
                'query' => $arguments,
            ]);
        } else {
            $response = $this->client->request($method, $uri, [
                'body' => json_encode($arguments, JSON_THROW_ON_ERROR),
            ]);
        }

        logger()->debug(
            sprintf(
                '[calendar][discord] [http %d, %s] %s -> /%s',
                $response->getStatusCode(),
                $response->getReasonPhrase(),
                $method,
                $uri
            )
        );

        if ($response->getStatusCode() === 204) {
            return null;
        }

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function getGuildId(): string
    {
        return $this->guild_id;
    }

    /**
     * @throws DiscordActionException
     */
    public static function createGuildEvent(GuildEvent $event): GuildEvent
    {
        try {
            $client = DiscordClient::getInstance();

            logger()->debug('Creating guild event', $event->toArray());

            $json = $client->sendCall('POST', '/guilds/{guild.id}/scheduled-events', array_merge_recursive(
                [
                    'guild.id' => $client->getGuildId(),
                ],
                $event->toArray()
            ));

            return GuildEvent::fromDiscordResponse($json);
        } catch (GuzzleException|JsonException|DiscordSettingsException|SettingException $e) {
            logger()->error(sprintf('[calendar][discord] %s', $e->getMessage()));
            throw new DiscordActionException(sprintf('Unable to create operation %s on discord', $event->name),
                0,
                $e);
        }
    }

    /**
     * @throws DiscordActionException
     */
    public static function getGuildEvent(GuildEvent $event): GuildEvent
    {
        try {
            $client = DiscordClient::getInstance();

            $json = $client->sendCall('GET', '/guilds/{guild.id}/scheduled-events/{guild_scheduled_event.id}', [
                'guild.id' => $client->getGuildId(),
                'guild_scheduled_event.id' => $event->id,
            ]);

            return GuildEvent::fromDiscordResponse($json);

        } catch (GuzzleException|JsonException|DiscordSettingsException|SettingException $e) {
            logger()->error(sprintf('[calendar][discord] %s', $e->getMessage()));
            throw new DiscordActionException(sprintf('Unable to retrieve guild event with id %s', $event->id),
                0,
                $e);
        }
    }

    /**
     * @throws DiscordActionException
     */
    public static function listGuildEvents(): array
    {
        try {
            $client = DiscordClient::getInstance();

            $json = $client->sendCall('GET', '/guilds/{guild.id}/scheduled-events', [
                'guild.id' => $client->getGuildId(),
            ]);

            $results = [];

            foreach ($json as $item) {
                $results[] = GuildEvent::fromDiscordResponse($item);
            }

            return $results;
        } catch (GuzzleException|JsonException|DiscordSettingsException|SettingException $e) {
            logger()->error(sprintf('[calendar][discord] %s', $e->getMessage()));
            throw new DiscordActionException('Unable to retrieve guild events', 0, $e);
        }
    }

    /**
     * @throws DiscordActionException
     */
    public static function modifyGuildEvent(string $id, array $params): GuildEvent
    {
        try {
            $client = DiscordClient::getInstance();

            $json = $client->sendCall('PATCH', '/guilds/{guild.id}/scheduled-events/{guild_scheduled_event.id}', array_merge_recursive(
                [
                    'guild.id' => $client->getGuildId(),
                    'guild_scheduled_event.id' => $id,
                ],
                $params
            ));

            return GuildEvent::fromDiscordResponse($json);
        } catch (GuzzleException|JsonException|DiscordSettingsException|SettingException $e) {
            logger()->error(sprintf('[calendar][discord] %s', $e->getMessage()));
            throw new DiscordActionException('Unable to retrieve guild events', 0, $e);
        }
    }

    /**
     * @throws DiscordActionException
     */
    public static function deleteGuildEvent(string $id): void
    {
        try {
            $client = DiscordClient::getInstance();

            $client->sendCall('DELETE', '/guilds/{guild.id}/scheduled-events/{guild_scheduled_event.id}',
                [
                    'guild.id' => $client->getGuildId(),
                    'guild_scheduled_event.id' => $id,
                ]
            );
        } catch (GuzzleException|JsonException|DiscordSettingsException|SettingException $e) {
            logger()->error(sprintf('[calendar][discord] %s', $e->getMessage()));
            throw new DiscordActionException('Unable to retrieve guild events', 0, $e);
        }
    }

    /**
     * @throws DiscordActionException
     */
    public static function getGuildEventUsers(GuildEvent $event): array
    {
        try {
            $client = DiscordClient::getInstance();

            $users = $client->sendCall('GET', '/guilds/{guild.id}/scheduled-events/{guild_scheduled_event.id}/users',
                [
                    'guild.id' => $client->getGuildId(),
                    'guild_scheduled_event.id' => $event->id,
                ]);

            return $users;
        } catch (GuzzleException|JsonException|DiscordSettingsException|SettingException $e) {
            logger()->error(sprintf('[calendar][discord] %s', $e->getMessage()));
            throw new DiscordActionException('Unable to retrieve guild events', 0, $e);
        }
    }

    public static function getGuildChannels(): Collection
    {
        try {
            $client = DiscordClient::getInstance();

            $channels = $client->sendCall('GET', '/guilds/{guild.id}/channels', [
                'guild.id' => $client->getGuildId(),
            ]);

            return collect($channels)->map(function ($c) {
                return (object)$c;
            });
        } catch (GuzzleException|JsonException|DiscordSettingsException|SettingException $e) {
            logger()->error(sprintf('[calendar][discord] %s', $e->getMessage()));

            return collect();
        }
    }

    public static function getVoiceChannels(): Collection
    {
        return self::getGuildChannels()->filter(function ($channel) {
            return $channel->type == 2;
        });
    }
}
