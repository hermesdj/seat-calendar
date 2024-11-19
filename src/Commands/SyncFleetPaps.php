<?php

namespace Seat\Kassie\Calendar\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Kassie\Calendar\Bus\FleetBus;
use Seat\Kassie\Calendar\Models\Operation;

class SyncFleetPaps extends Command
{
    protected $signature = 'calendar:paps:sync';

    protected $description = 'Sync fleet members in game for paps';

    public function handle(): void
    {
        $now = Carbon::now('UTC')->toDateTimeString();

        logger()->debug("Search active operations from now: $now");

        $query = Operation::where('is_cancelled', 0)
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->whereNotNull('fc_character_id');

        logger()->debug($query->toSql());

        $activeOps = $query->get();

        if ($activeOps->isEmpty()) {
            logger()->debug('No active operations found.');
        }

        $activeOps->each(function ($op) {
            try {
                $token = RefreshToken::findOrFail($op->fc_character_id);

                (new FleetBus($op->id, $token))->fire();

                logger()->debug('Started process paps', [
                    'operation_id' => $op->id,
                    'flow' => 'character',
                    'token' => $token->character_id,
                ]);
            } catch (ModelNotFoundException $e) {
                logger()->warn("Fleet commander is not already linked to SeAT. Unable to PAP the op $op->id.");
            }
        });
    }
}
