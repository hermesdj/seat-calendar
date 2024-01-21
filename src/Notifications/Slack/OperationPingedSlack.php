<?php

namespace Seat\Kassie\Calendar\Notifications\Slack;

use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Queue\SerializesModels;
use Seat\Kassie\Calendar\Helpers\Helper;
use Seat\Notifications\Notifications\AbstractSlackNotification;
use Seat\Services\Exceptions\SettingException;

class OperationPingedSlack extends AbstractSlackNotification
{
    use SerializesModels;

    private array $operations;

    public function __construct($operations)
    {
        $this->operations = $operations;
    }

    /**
     * @throws SettingException
     */
    public function populateMessage(SlackMessage $message, $notifiable): void
    {
        $ops = $this->operations;

        $message->success()
            ->from('SeAT Calendar', ':calendar:');

        if (count($ops) == 1) {
            $attachment = Helper::BuildSlackNotificationAttachment($ops[0]);
            $message
                ->content(trans('calendar::seat.notification_ping_operation') . '*' . trans('calendar::seat.starts_in') . ' ' . $notifiable->starts_in . '*')
                ->attachment($attachment);
        } else {
            $message->attachment(function ($attachment) use ($ops) {
                $attachment->title(trans('calendar::seat.notification_ping_operation_multiple'));
                foreach ($ops as $op) {
                    $url = url('/calendar/operation', [$op->id]);
                    $attachment->field(function ($field) use ($op, $url) {
                        $field->long()
                            ->title($op->title, $url)
                            ->content(trans('calendar::seat.notification_ping_operation') . '*' . trans('calendar::seat.starts_in') . ' ' . $op->starts_in . '*');
                    });
                }
            });
        }
    }
}