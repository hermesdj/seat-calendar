<?php

namespace Seat\Kassie\Calendar\Notifications\Slack;

use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Queue\SerializesModels;
use Seat\Kassie\Calendar\Helpers\Helper;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Notifications\Notifications\AbstractSlackNotification;

class OperationPostedSlack extends AbstractSlackNotification
{
    use SerializesModels;

    private Operation $operation;

    public function __construct($operation)
    {
        $this->operation = $operation;
    }

    public function populateMessage(SlackMessage $message, $notifiable): void
    {
        $attachment = Helper::BuildSlackNotificationAttachment($this->operation);

        $message->success()
            ->from('SeAT Calendar', ':calendar:')
            ->content(trans('calendar::notifications.notification_new_operation', locale: setting('kassie.calendar.notify_locale')))
            ->attachment($attachment);
    }
}
