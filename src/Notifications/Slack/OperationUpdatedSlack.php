<?php

namespace Seat\Kassie\Calendar\Notifications\Slack;

use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Queue\SerializesModels;
use Seat\Kassie\Calendar\Helpers\Helper;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Notifications\Notifications\AbstractSlackNotification;
use Seat\Services\Exceptions\SettingException;

class OperationUpdatedSlack extends AbstractSlackNotification
{
    use SerializesModels;
    private Operation $operation;

    public function __construct($operation)
    {
        $this->operation = $operation;
    }

    /**
     */
    public function populateMessage(SlackMessage $message, $notifiable): void
    {
        $attachment = Helper::BuildSlackNotificationAttachment($this->operation);

        $message->warning()
            ->from('SeAT Calendar', ':calendar:')
            ->content(trans('calendar::notifications.notification_edit_operation'))
            ->attachment($attachment);
    }
}