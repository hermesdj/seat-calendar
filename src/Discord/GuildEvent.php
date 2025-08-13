<?php

namespace Seat\Kassie\Calendar\Discord;

use Illuminate\Support\Str;
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
        $event = new GuildEvent;
        $event->set($json);

        return $event;
    }

    public static function fromOperation(Operation $operation): GuildEvent
    {
        $event = new GuildEvent;

        if ($operation->discord_guild_event_id != null) {
            $event->set([
                'id' => $operation->discord_guild_event_id,
            ]);
        }

        if ($operation->discord_voice_channel_id != null) {
            $event->set([
                'channel_id' => $operation->discord_voice_channel_id,
                'entity_type' => 2,
                'entity_metadata' => null,
                'description' => Str::limit($operation->description, 800, '(...)') . ' ' . url('/calendar/operation', [$operation->id]),
            ]);
        } else {
            $event->set([
                'entity_type' => 3,
                'entity_metadata' => [
                    'location' => url('/calendar/operation', [$operation->id]),
                ],
                'description' => Str::limit($operation->description, 900, '(...)'),
            ]);
        }

        $event->set([
            'name' => Str::limit($operation->title, 100, '(...)'),
            'privacy_level' => 2, // GUILD MEMBERS ONLY
            'scheduled_start_time' => $operation->start_at
        ]);

        if ($operation->end_at != null) {
            $event->set([
                'scheduled_end_time' => $operation->end_at,
            ]);
        }

        return $event;
    }

    public function toArray(): array
    {
        return (array)$this;
    }
}
