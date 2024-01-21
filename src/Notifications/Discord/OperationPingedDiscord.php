<?php

namespace Seat\Kassie\Calendar\Notifications\Discord;

use Illuminate\Queue\SerializesModels;
use Seat\Kassie\Calendar\Helpers\Helper;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Notifications\Notifications\AbstractDiscordNotification;
use Seat\Notifications\Services\Discord\Messages\DiscordMessage;

class OperationPingedDiscord extends AbstractDiscordNotification
{
    use SerializesModels;

    private array $operations;

    public function __construct($operations)
    {
        $this->operations = $operations;
    }

    protected function populateMessage(DiscordMessage $message, $notifiable)
    {
        $ops = $this->operations;

        $message->success()
            ->from('SeAT Calendar', ':calendar:');

        if (count($ops) == 1) {
            $attachment = Helper::BuildDiscordOperationEmbed($ops[0]);
            $message
                ->content(trans('calendar::notifications.notification_ping_operation') . '*' . trans('calendar::seat.starts_in') . ' ' . $notifiable->starts_in . '*')
                ->embed($attachment);
        } else {
            $message->embed(function ($embed) use ($ops) {
                $embed->title(trans('calendar::notifications.notification_ping_operation_multiple'));
                foreach ($ops as $op) {
                    $url = url('/calendar/operation', [$op->id]);
                    $embed->field(function ($field) use ($op, $url) {
                        $field->long()
                            ->title($op->title, $url)
                            ->content(trans('calendar::notifications.notification_ping_operation') . '*' . trans('calendar::seat.starts_in') . ' ' . $op->starts_in . '*');
                    });
                }
            });
        }
    }
}