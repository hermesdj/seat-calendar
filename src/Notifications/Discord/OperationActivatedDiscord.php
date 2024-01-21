<?php

namespace Seat\Kassie\Calendar\Notifications\Discord;

use Illuminate\Queue\SerializesModels;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Notifications\Notifications\AbstractDiscordNotification;
use Seat\Notifications\Services\Discord\Messages\DiscordMessage;

class OperationActivatedDiscord extends AbstractDiscordNotification
{
    use SerializesModels;
    private Operation $operation;

    public function __construct($operation)
    {
        $this->operation = $operation;
    }

    protected function populateMessage(DiscordMessage $message, $notifiable)
    {
        // TODO: Implement populateMessage() method.
    }
}