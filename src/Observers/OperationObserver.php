<?php

namespace Seat\Kassie\Calendar\Observers;

use Seat\Kassie\Calendar\Discord\DiscordAction;
use Seat\Kassie\Calendar\Models\Operation;

/**
 * Class OperationObserver.
 *
 * @package Seat\Kassie\Calendar\Observers
 */
class OperationObserver
{
    /**
     * @param Operation $operation
     */
    public function created(Operation $operation): void
    {
        DiscordAction::syncWithDiscord("created", $operation);
    }

    public function deleted(Operation $operation): void
    {
        DiscordAction::syncWithDiscord("deleted", $operation);
    }
}
