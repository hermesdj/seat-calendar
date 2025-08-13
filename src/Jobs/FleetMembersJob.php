<?php

namespace Seat\Kassie\Calendar\Jobs;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Kassie\Calendar\Models\Pap;
use Seat\Kassie\Calendar\Models\PapFleet;

class FleetMembersJob extends AbstractAuthCharacterJob
{
    protected $method = 'get';

    protected $endpoint = '/fleets/{fleet_id}/members/';

    protected $version = 'v1';

    protected $scope = 'esi-fleets.read_fleet.v1';

    protected array $tags = ['calendar', 'character', 'fleet'];

    protected int $operation_id;

    public function __construct($operation_id, RefreshToken $token)
    {
        $this->operation_id = $operation_id;
        parent::__construct($token);
    }

    public function handle(): void
    {
        parent::handle();

        $fleet = PapFleet::where('operation_id', $this->operation_id)
            ->where('fleet_commander_id', $this->getCharacterId())
            ->first();

        if (! is_null($fleet)) {
            $response = $this->retrieve([
                'fleet_id' => $fleet->fleet_id,
            ]);

            $op = Operation::with('tags')->find($this->operation_id);

            $members = $response->getBody();

            $value = 0;

            if (! is_null($op) && $op->tags->count() > 0) {
                $value = $op->tags->max('quantifier');
            }

            collect($members)->each(function ($member) use ($value) {
                $dt = carbon($member->join_time);

                Pap::updateOrCreate([
                    'character_id' => $member->character_id,
                    'operation_id' => $this->operation_id,
                ], [
                    'ship_type_id' => $member->ship_type_id,
                    'join_time' => $dt->toDateTimeString(),
                    'value' => $value,
                    'week' => $dt->weekOfMonth,
                    'month' => $dt->month,
                    'year' => $dt->year,
                ]);
            });
        } else {
            logger()->warning("No fleet found for operation $this->operation_id and fleet commander {$this->getCharacterId()}");
        }
    }
}
