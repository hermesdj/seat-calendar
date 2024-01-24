<?php

namespace Seat\Kassie\Calendar\Notifications\Discord;

use Illuminate\Queue\SerializesModels;
use Seat\Kassie\Calendar\Helpers\Helper;
use Seat\Notifications\Notifications\AbstractDiscordNotification;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbed;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbedField;
use Seat\Notifications\Services\Discord\Messages\DiscordMessage;

class OperationPingedDiscord extends AbstractDiscordNotification
{
    use SerializesModels;

    private array $operations;

    public function __construct($operations)
    {
        $this->operations = $operations;
    }

    protected function populateMessage(DiscordMessage $message, $notifiable): void
    {
        $ops = $this->operations;

        $message->success()
            ->from('SeAT Calendar', config('buyback.discord.webhook.logoUrl'));

        if (count($ops) == 1) {
            $operation = $ops[0];
            $attachment = Helper::BuildDiscordOperationEmbed($operation);
            $message
                ->content(trans('calendar::notifications.notification_ping_operation') . '*' . trans('calendar::seat.starts_in') . ' ' . $operation->getStartsInAttribute() . '*')
                ->embed($attachment);
        } else {
            $message->embed(function (DiscordEmbed $embed) use ($ops): void {
                $embed->title(trans('calendar::notifications.notification_ping_operation_multiple'));
                foreach ($ops as $op) {
                    $url = url('/calendar/operation', [$op->id]);
                    $embed->field(function (DiscordEmbedField $field) use ($op, $url) {
                        $field->long()
                            ->name('[' . $op->title . '](' . $url . ')')
                            ->value(trans('calendar::notifications.notification_ping_operation') . '*' . trans('calendar::seat.starts_in') . ' ' . $op->getStartsInAttribute() . '*');
                    });
                }
            });
        }
    }
}