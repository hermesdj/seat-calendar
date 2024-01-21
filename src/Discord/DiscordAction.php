<?php

namespace Seat\Kassie\Calendar\Discord;

use Seat\Kassie\Calendar\Models\Operation;

class DiscordAction
{
    protected string $actionType;

    public function __construct()
    {
    }

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
            case "created":
                $this->createGuildEvent($operation);
                break;
            case "cancelled":
                $this->cancelGuildEvent($operation);
                break;
            case "activated":
                $this->activateGuildEvent($operation);
                break;
            case "ended":
                $this->endGuildEvent($operation);
                break;
            case "updated":
                $this->updateGuildEvent($operation);
                break;
            case "deleted":
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
        $event = GuildEvent::fromOperation($operation);
        $event->status = 4; // CANCELLED

        DiscordClient::modifyGuildEvent($operation->discord_guild_event_id, [
            'status' => 4
        ]);
    }

    /**
     * @throws DiscordActionException
     */
    public function activateGuildEvent(Operation $operation): void
    {
        // Operation was cancelled and is being reactivated
        DiscordClient::deleteGuildEvent($operation->discord_guild_event_id);
        // We recreate the event because discord does not allow a transition from CANCELED state
        $this->createGuildEvent($operation);
    }

    /**
     * @throws DiscordActionException
     */
    public function endGuildEvent(Operation $operation): void
    {
        DiscordClient::modifyGuildEvent($operation->discord_guild_event_id, [
            'status' => 3 // COMPLETED
        ]);
    }

    /**
     * @throws DiscordActionException
     */
    public function updateGuildEvent(Operation $operation): void
    {
        $event = GuildEvent::fromOperation($operation);
        DiscordClient::modifyGuildEvent($operation->discord_guild_event_id, $event->toArray());
    }

    /**
     * @throws DiscordActionException
     */
    public function createGuildEvent(Operation $operation): void
    {
        $event = DiscordClient::createGuildEvent(GuildEvent::fromOperation($operation));
        $operation->discord_guild_event_id = $event->id;
        $operation->save();
    }
}