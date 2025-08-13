<?php

namespace Seat\Kassie\Calendar\Notifications;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Seat\Notifications\Models\NotificationGroup;
use Seat\Notifications\Traits\NotificationDispatchTool;

class NotificationDispatcher
{
    use NotificationDispatchTool;

    public static function dispatchOperationCreated($operation): void
    {
        logger()->debug("NotificationDispatcher::created $operation->id");
        self::dispatch('seat_calendar_operation_posted', $operation);
    }

    public static function dispatchOperationCancelled($operation): void
    {
        logger()->debug('New operation is cancelled, sending cancelled event');
        self::dispatch('seat_calendar_operation_cancelled', $operation);
    }

    public static function dispatchOperationActivated($operation): void
    {
        logger()->debug('New operation is reactivated, sending activated event');
        self::dispatch('seat_calendar_operation_activated', $operation);
    }

    public static function dispatchOperationEnded($operation): void
    {
        logger()->debug('Operation has ended');
        self::dispatch('seat_calendar_operation_ended', $operation);
    }

    public static function dispatchOperationUpdated($operation): void
    {
        self::dispatch('seat_calendar_operation_updated', $operation);
    }

    public static function dispatchOperationsPinged(Collection $operations): void
    {
        foreach ($operations->all() as $operation) {
            self::dispatch('seat_calendar_operation_pinged', $operation);
        }
    }

    private static function dispatch($alertType, $operation): void
    {
        logger()->debug("dispatch notification $alertType on operation $operation->title");
        $handlers = config(sprintf('notifications.alerts.%s.handlers', $alertType), []);

        if (empty($handlers)) {
            logger()->debug('Unsupported notification type', [
                'type' => $alertType,
            ]);

            return;
        }

        $groups = NotificationGroup::with('alerts')
            ->whereHas('alerts', function ($query) use ($alertType): void {
                $query->where('alert', $alertType);
            })->get();

        $integrations = self::mapGroups($groups);

        $allowedIntegrationIds = collect();

        if ($operation->tags->isEmpty()) {
            logger()->debug('No tags found');

            return;
        }

        foreach ($operation->tags as $tag) {
            $tagIntegrations = $tag->integrations()->get();
            foreach ($tagIntegrations as $integration) {
                $allowedIntegrationIds->add($integration->id);
            }
        }

        if ($allowedIntegrationIds->isEmpty()) {
            logger()->debug('No integrations defined by tags found');

            return;
        }

        $integrations = $integrations->filter(function ($integration) use ($allowedIntegrationIds) {
            return $allowedIntegrationIds->contains($integration->id);
        });

        if ($integrations->isEmpty()) {
            logger()->debug('No integration found');

            return;
        }

        $integrations->each(function ($integration) use ($operation, $handlers) {
            if (array_key_exists($integration->channel, $handlers)) {
                $handler = $handlers[$integration->channel];
                $notification = new $handler($operation);
                $notification->setMentions($integration->mentions);

                Notification::route($integration->channel, $integration->route)
                    ->notifyNow($notification);
            }
        });
    }

    private static function mapGroups($groups)
    {
        return $groups->map(function ($group) {
            return $group->integrations->map(function ($channel) use ($group) {
                $setting = (array) $channel->settings;
                $key = array_key_first($setting);
                $route = $setting[$key];

                return (object) [
                    'id' => $channel->id,
                    'channel' => $channel->type,
                    'route' => $route,
                    'mentions' => $group->mentions->filter(function ($mention) use ($channel) {
                        return $mention->getType()->type = $channel->type;
                    }),
                ];
            });
        })->flatten();
    }
}
