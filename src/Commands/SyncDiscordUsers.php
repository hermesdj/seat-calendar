<?php

namespace Seat\Kassie\Calendar\Commands;

use Illuminate\Console\Command;
use Seat\Kassie\Calendar\Discord\DiscordActionException;
use Seat\Kassie\Calendar\Discord\DiscordClient;
use Seat\Kassie\Calendar\Discord\GuildEvent;
use Seat\Kassie\Calendar\Models\Attendee;
use Seat\Kassie\Calendar\Models\Operation;
use Warlof\Seat\Connector\Models\User;

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

        if (count($ops) === 0) {
            logger()->debug('No events synced with discord currently in DB');
            return;
        }

        foreach ($ops as $op) {
            $guildEventUsers = DiscordClient::getGuildEventUsers(GuildEvent::fromOperation($op));
            $users = [];

            if (count($guildEventUsers) === 0) {
                logger()->debug('No users participating on discord for op ' . $op->id);
                continue;
            }

            foreach ($guildEventUsers as $user) {
                $userId = $user['user_id'];
                $response = $user['response'];

                logger()->debug("User $userId response is $response");

                $connectorUser = User::where('connector_type', 'discord')
                    ->where('connector_id', $userId)
                    ->first();

                if (!is_null($connectorUser)) {
                    $seatUser = \Seat\Web\Models\User::find($connectorUser->user_id);

                    if (!is_null($seatUser)) {
                        $users[] = [
                            'guildUser' => $user,
                            'seatUser' => $seatUser
                        ];
                    }
                }
            }

            // Sync users attending locally but no longer attending on the guild event => attendee should be deleted if the comment is equal to 'synced_from_discord_event'
            $usersSyncedFromDiscord = Attendee::where('comment', 'synced_from_discord_event')
                ->where('operation_id', $op->id)->get();

            foreach ($usersSyncedFromDiscord as $attendee) {
                $foundUsers = array_filter($users, function ($user) use ($attendee) {
                    return $user['seatUser']->id === $attendee->user_id;
                });

                if (count($foundUsers) === 0) {
                    $attendee->delete();
                }
            }

            // Now create missing attendees
            foreach ($users as $user) {
                $name = $user['seatUser']->name;
                logger()->debug("adding missing attendee $name");
                Attendee::updateOrCreate(
                    [
                        'operation_id' => $op->id,
                        'character_id' => $user['seatUser']->main_character_id
                    ],
                    [
                        'user_id' => $user['seatUser']->id,
                        'status' => 'yes',
                        'comment' => 'synced_from_discord_event'
                    ]
                );
            }
        }
    }
}