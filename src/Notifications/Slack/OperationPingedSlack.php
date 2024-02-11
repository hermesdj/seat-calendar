<?php

namespace Seat\Kassie\Calendar\Notifications\Slack;

use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackAttachmentField;
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
            $operation = $ops[0];
            $attachment = Helper::BuildSlackNotificationAttachment($operation);
            $message
                ->content(trans('calendar::notifications.notification_ping_operation') . '*' . trans('calendar::seat.starts_in') . ' ' . $operation->getStartsInAttribute() . '*')
                ->attachment($attachment);
        } else {
            $message->attachment(function (SlackAttachment $attachment) use ($ops) {
                $attachment->title(trans('calendar::notifications.notification_ping_operation_multiple'));
                foreach ($ops as $op) {
                    $url = url('/calendar/operation', [$op->id]);
                    $attachment->field(function (SlackAttachmentField $field) use ($op, $url) {
                        $field->long()
                            ->title('<' . $op->title . '|' . $url . '>',)
                            ->content(trans('calendar::notifications.notification_ping_operation') . '*' . trans('calendar::seat.starts_in') . ' ' . $op->getStartsInAttribute() . '*');
                    });
                }
            });
        }
    }
}