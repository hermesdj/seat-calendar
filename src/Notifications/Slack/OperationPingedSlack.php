<?php

namespace Seat\Kassie\Calendar\Notifications\Slack;

use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Queue\SerializesModels;
use Seat\Kassie\Calendar\Helpers\Helper;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Notifications\Notifications\AbstractSlackNotification;

class OperationPingedSlack extends AbstractSlackNotification
{
    use SerializesModels;

    private Operation $operation;

    public function __construct(Operation $operation)
    {
        $this->operation = $operation;
    }

    public function populateMessage(SlackMessage $message, $notifiable): void
    {

        $message->success()
            ->from('SeAT Calendar', ':calendar:')
            ->content(trans('calendar::notifications.notification_ping_operation', locale: setting('kassie.calendar.notify_locale')).'*'.trans('calendar::seat.starts_in', locale: setting('kassie.calendar.notify_locale')).' '.$this->operation->getStartsInAttribute().'*')
            ->attachment(Helper::BuildSlackNotificationAttachment($this->operation));
    }
}
