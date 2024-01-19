<?php

namespace Seat\Kassie\Calendar\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Seat\Kassie\Calendar\Helpers\Helper;
use Seat\Services\Exceptions\SettingException;

/**
 * Class OperationPosted.
 *
 * @package Seat\Kassie\Calendar\Notifications
 */
class OperationPosted extends Notification
{
    use Queueable;

    /**
     * @param $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['slack'];
    }

    /**
     * @param $notifiable
     * @return SlackMessage
     * @throws SettingException
     */
    public function toSlack($notifiable): SlackMessage
    {
        $attachment = Helper::BuildSlackNotificationAttachment($notifiable);

        return (new SlackMessage)
            ->success()
            ->from('SeAT Calendar', ':calendar:')
            ->content(trans('calendar::seat.notification_new_operation'))
            ->attachment($attachment);
    }
}
