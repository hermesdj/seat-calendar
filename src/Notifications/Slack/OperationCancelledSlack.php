<?php

namespace Seat\Kassie\Calendar\Notifications\Slack;

use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Queue\SerializesModels;
use Seat\Kassie\Calendar\Helpers\Helper;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Notifications\Notifications\AbstractSlackNotification;
use Seat\Services\Exceptions\SettingException;

class OperationCancelledSlack extends AbstractSlackNotification
{
    use SerializesModels;
    private Operation $operation;

    public function __construct($operation)
    {
        $this->operation = $operation;
    }

    /**
     * @throws SettingException
     */
    public function populateMessage(SlackMessage $message, $notifiable): void
    {
        $attachment = Helper::BuildSlackNotificationAttachment($this->operation);

        $message->error()
            ->from('SeAT Calendar', ':calendar:')
            ->content(trans('calendar::notifications.notification_cancel_operation'))
            ->attachment($attachment);
    }
}