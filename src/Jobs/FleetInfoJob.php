<?php

namespace Seat\Kassie\Calendar\Jobs;

use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Kassie\Calendar\Exceptions\PapsException;
use Seat\Kassie\Calendar\Models\PapFleet;

class FleetInfoJob extends AbstractAuthCharacterJob
{
    protected $method = 'get';

    protected $endpoint = '/characters/{character_id}/fleet/';

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

        try {
            $response = $this->retrieve([
                'character_id' => $this->getCharacterId(),
            ]);
        } catch (RequestFailedException $e) {
            throw new PapsException('Fleet could not be tracked: '.$e->getMessage());
        }

        $fleet = $response->getBody();

        // First remove all fleets from this commander first (a character can only be in one fleet in game)
        PapFleet::where('fleet_commander_id', $this->getCharacterId())
            ->whereNotIn('fleet_id', [$fleet->fleet_id])
            ->delete();

        // Store fleet info in the DB
        PapFleet::firstOrNew([
            'fleet_commander_id' => $this->getCharacterId(),
            'operation_id' => $this->operation_id,
        ])->fill([
            'fleet_id' => $fleet->fleet_id,
        ])->save();
    }
}
