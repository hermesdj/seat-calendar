<?php

return [
    'seat_calendar_operation_activated' => [
        'label' => 'calendar::seat.notification_operation_activated_label',
        'handlers' => [
            'slack' => \Seat\Kassie\Calendar\Notifications\Slack\OperationActivatedSlack::class,
            'discord' => \Seat\Kassie\Calendar\Notifications\Discord\OperationActivatedDiscord::class,
            'mail' => \Seat\Kassie\Calendar\Notifications\Mail\OperationActivatedMail::class,
        ],
    ],
    'seat_calendar_operation_cancelled' => [
        'label' => 'calendar::seat.notification_operation_cancelled_label',
        'handlers' => [
            'slack' => \Seat\Kassie\Calendar\Notifications\Slack\OperationCancelledSlack::class,
            'discord' => \Seat\Kassie\Calendar\Notifications\Discord\OperationCancelledDiscord::class,
            'mail' => \Seat\Kassie\Calendar\Notifications\Mail\OperationCancelledMail::class,
        ],
    ],
    'seat_calendar_operation_ended' => [
        'label' => 'calendar::seat.notification_operation_ended_label',
        'handlers' => [
            'slack' => \Seat\Kassie\Calendar\Notifications\Slack\OperationEndedSlack::class,
            'discord' => \Seat\Kassie\Calendar\Notifications\Discord\OperationEndedDiscord::class,
            'mail' => \Seat\Kassie\Calendar\Notifications\Mail\OperationEndedMail::class,
        ],
    ],
    'seat_calendar_operation_pinged' => [
        'label' => 'calendar::seat.notification_operation_pinged_label',
        'handlers' => [
            'slack' => \Seat\Kassie\Calendar\Notifications\Slack\OperationPingedSlack::class,
            'discord' => \Seat\Kassie\Calendar\Notifications\Discord\OperationPingedDiscord::class,
            'mail' => \Seat\Kassie\Calendar\Notifications\Mail\OperationPingedMail::class,
        ],
    ],
    'seat_calendar_operation_posted' => [
        'label' => 'calendar::seat.notification_operation_posted_label',
        'handlers' => [
            'slack' => \Seat\Kassie\Calendar\Notifications\Slack\OperationPostedSlack::class,
            'discord' => \Seat\Kassie\Calendar\Notifications\Discord\OperationPostedDiscord::class,
            'mail' => \Seat\Kassie\Calendar\Notifications\Mail\OperationPostedMail::class,
        ],
    ],
    'seat_calendar_operation_updated' => [
        'label' => 'calendar::seat.notification_operation_updated_label',
        'handlers' => [
            'slack' => \Seat\Kassie\Calendar\Notifications\Slack\OperationUpdatedSlack::class,
            'discord' => \Seat\Kassie\Calendar\Notifications\Discord\OperationUpdatedDiscord::class,
            'mail' => \Seat\Kassie\Calendar\Notifications\Mail\OperationUpdatedMail::class,
        ],
    ],
];
