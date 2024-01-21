<?php

namespace Seat\Kassie\Calendar\Notifications\Mail;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Notifications\Notifications\AbstractMailNotification;

class OperationPostedMail extends AbstractMailNotification
{
    use SerializesModels;
    private Operation $operation;

    public function __construct($operation)
    {
        $this->operation = $operation;
    }

    public function populateMessage(MailMessage $message, $notifiable)
    {
        // TODO
    }
}