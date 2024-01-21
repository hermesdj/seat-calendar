<?php

namespace Seat\Kassie\Calendar\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Seat\Kassie\Calendar\Discord\DiscordActionException;
use Seat\Kassie\Calendar\Discord\DiscordClient;
use Seat\Kassie\Calendar\Discord\GuildEvent;
use Seat\Kassie\Calendar\Models\Operation;

class SyncDiscordUsers extends Command
{
    protected $signature = 'calendar:discord:sync';

    protected $description = 'Sync users from discord guild events';

    /**
     * @throws DiscordActionException
     */
    public function handle(): void
    {
        $ops = Operation::where('is_cancelled', false)
            ->whereNotNull('discord_guild_event_id')
            ->get();

        foreach ($ops as $op) {
            $users = DiscordClient::getGuildEventUsers(GuildEvent::fromOperation($op));
            Log::debug(print_r($users, true));
        }
    }
}