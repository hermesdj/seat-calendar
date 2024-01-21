<?php

namespace Seat\Kassie\Calendar\Discord;

use Seat\Kassie\Calendar\Models\Operation;

class GuildEvent
{
    public function set($data): void
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public static function fromDiscordResponse($json): GuildEvent
    {
        $event = new GuildEvent();
        $event->set($json);
        return $event;
    }

    public static function fromOperation(Operation $operation): GuildEvent
    {
        $event = new GuildEvent();
        $event->set([
            'id' => $operation->discord_guild_event_id ?: null,
            'entity_metadata' => [
                'location' => url('/calendar/operation', [$operation->id]),
            ],
            'name' => $operation->title,
            'privacy_level' => 2, // GUILD MEMBERS ONLY
            'scheduled_start_time' => $operation->start_at,
            'scheduled_end_time' => $operation->end_at,
            'description' => $operation->description,
            'entity_type' => 3, // EXTERNAL
        ]);
        return $event;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'entity_metadata' => [
                'location' => $this->entity_metadata['location'],
            ],
            'name' => $this->name,
            'privacy_level' => $this->privacy_level,
            'scheduled_start_time' => $this->scheduled_start_time,
            'scheduled_end_time' => $this->scheduled_end_time,
            'description' => $this->description,
            'entity_type' => $this->entity_type
        ];
    }
}