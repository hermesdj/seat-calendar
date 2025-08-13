<?php

namespace Seat\Kassie\Calendar\Jobs;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\RefreshToken;
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

        if (!is_null($fleet)) {
            $response = $this->retrieve([
                'fleet_id' => $fleet->fleet_id,
            ]);

            $members = $response->getBody();

            collect($members)->each(function ($member) {
                logger()->debug("Collected member data", $member);
                $pap = Pap::firstOrCreate([
                    'character_id' => $member->character_id,
                    'operation_id' => $this->operation_id,
                ], [
                    'ship_type_id' => $member->ship_type_id,
                    'join_time' => carbon($member->join_time)->toDateTimeString(),
                ]);

                if ($pap->ship_type_id !== $member->ship_type_id) {
                    $pap->ship_type_id = $member->ship_type_id;
                    $pap->save();
                }
            });
        } else {
            logger()->warning("No fleet found for operation $this->operation_id and fleet commander {$this->getCharacterId()}");
        }
    }
}
