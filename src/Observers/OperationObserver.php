<?php

namespace Seat\Kassie\Calendar\Observers;

use Seat\Kassie\Calendar\Discord\DiscordAction;
use Seat\Kassie\Calendar\Models\Operation;

/**
 * Class OperationObserver.
 */
class OperationObserver
{
    public function created(Operation $operation): void
    {
        DiscordAction::syncWithDiscord('created', $operation);
    }

    public function updated(Operation $operation): void
    {
        DiscordAction::syncWithDiscord('updated', $operation);
    }

    public function deleted(Operation $operation): void
    {
        DiscordAction::syncWithDiscord('deleted', $operation);
    }
}
