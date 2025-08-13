<?php

namespace Seat\Kassie\Calendar\Jobs;

use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Exception\TemporaryEsiOutageException;
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

        logger()->info("Retrieving fleet information for character {$this->getCharacterId()}");

        try {
            $response = $this->retrieve([
                'character_id' => $this->getCharacterId(),
            ]);
        } catch (RequestFailedException|TemporaryEsiOutageException $e) {
            throw new PapsException('Fleet could not be tracked: '.$e->getMessage());
        }

        $fleet = $response->getBody();

        logger()->info("Retrieved Fleet info with id $fleet->fleet_id and role $fleet->role");

        // First remove all fleets from this commander first (a character can only be in one fleet in game)
        PapFleet::where('fleet_commander_id', $this->getCharacterId())
            ->whereNotIn('fleet_id', [$fleet->fleet_id])
            ->delete();

        if ($fleet->fleet_boss_id == $this->getCharacterId()) {
            // Store fleet info in the DB
            logger()->info("Storing fleet info for $fleet->fleet_id in $this->operation_id and fleet boss {$this->getCharacterId()}");
            PapFleet::firstOrNew([
                'fleet_commander_id' => $this->getCharacterId(),
                'operation_id' => $this->operation_id,
            ])->fill([
                'fleet_id' => $fleet->fleet_id,
            ])->save();
        } else {
            logger()->warning("Fleet boss is not the selected fleet commander for operation $this->operation_id, we cant sync fleet paps");
        }
    }
}
