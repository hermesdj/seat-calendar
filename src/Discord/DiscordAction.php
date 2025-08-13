<?php

namespace Seat\Kassie\Calendar\Discord;

use Seat\Kassie\Calendar\Models\Operation;
use Seat\Services\Exceptions\SettingException;

class DiscordAction
{
    protected string $actionType;

    public function __construct() {}

    public function setType(string $actionType): DiscordAction
    {
        $this->actionType = $actionType;

        return $this;
    }

    /**
     * @throws DiscordActionException
     */
    public function execute(Operation $operation): void
    {
        switch ($this->actionType) {
            case 'created':
                $this->createGuildEvent($operation);
                break;
            case 'cancelled':
                $this->cancelGuildEvent($operation);
                break;
            case 'activated':
                $this->activateGuildEvent($operation);
                break;
            case 'ended':
                $this->endGuildEvent($operation);
                break;
            case 'updated':
                $this->updateGuildEvent($operation);
                break;
            case 'deleted':
                $this->deleteGuildEvent($operation);
                break;
            default:
                throw new DiscordActionException("Unexpected actionType provided $this->actionType");
        }
    }

    /**
     * @throws DiscordActionException
     */
    public function cancelGuildEvent(Operation $operation): void
    {
        logger()->debug('Cancelled operation, settings cancelled status to guild event on discord');
        $event = GuildEvent::fromOperation($operation);
        $event->status = 4; // CANCELLED

        if ($operation->discord_guild_event_id) {
            DiscordClient::modifyGuildEvent($operation->discord_guild_event_id, [
                'status' => 4,
            ]);
        }
    }

    /**
     * @throws DiscordActionException
     */
    public function activateGuildEvent(Operation $operation): void
    {
        logger()->debug('Activated operation, recreating guild event on discord');
        // Operation was cancelled and is being reactivated
        if ($operation->discord_guild_event_id) {
            DiscordClient::deleteGuildEvent($operation->discord_guild_event_id);
        }
        // We recreate the event because discord does not allow a transition from CANCELED state
        $this->createGuildEvent($operation);
    }

    /**
     * @throws DiscordActionException
     */
    public function endGuildEvent(Operation $operation): void
    {
        if ($operation->discord_guild_event_id) {
            logger()->debug('Ending guild event by settings status 3 = COMPLETED');
            DiscordClient::modifyGuildEvent($operation->discord_guild_event_id, [
                'status' => 3, // COMPLETED
            ]);
        }
    }

    /**
     * @throws DiscordActionException
     */
    public function updateGuildEvent(Operation $operation): void
    {
        if ($operation->discord_guild_event_id) {
            logger()->debug('Updating guild event');
            $event = GuildEvent::fromOperation($operation);
            DiscordClient::modifyGuildEvent($operation->discord_guild_event_id, $event->toArray());
        } else {
            $this->createGuildEvent($operation);
        }
    }

    /**
     * @throws DiscordActionException
     */
    public function createGuildEvent(Operation $operation): void
    {
        logger()->info('Creating guild event on discord for operation', ['operation' => $operation]);
        $event = DiscordClient::createGuildEvent(GuildEvent::fromOperation($operation));
        $operation->discord_guild_event_id = $event->id;
        $operation->save();
    }

    /**
     * @throws DiscordActionException
     */
    public function deleteGuildEvent(Operation $operation): void
    {
        if ($operation->discord_guild_event_id) {
            logger()->debug("Deleting guild event on discord with id $operation->discord_guild_event_id");
            DiscordClient::deleteGuildEvent($operation->discord_guild_event_id);
            logger()->debug('Guild event has been deleted !');
        } else {
            logger()->info('No guild event to delete on discord because the operation does not have a guild event id');
        }
    }

    public static function syncWithDiscord($actionType, $operation): void
    {
        try {
            if (setting('kassie.calendar.discord_integration', true)) {
                (new self)
                    ->setType($actionType)
                    ->execute($operation);
                logger()->info("call discord action with type $actionType on operation $operation->title");
            } else {
                logger()->debug('Discord integration is not activated');
            }
        } catch (DiscordActionException|SettingException $e) {
            logger()->error('Error guild event on discord ', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
