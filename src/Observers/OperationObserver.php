<?php

namespace Seat\Kassie\Calendar\Observers;

use Carbon\Carbon;
use Seat\Kassie\Calendar\Discord\DiscordAction;
use Seat\Kassie\Calendar\Discord\DiscordActionException;
use Seat\Kassie\Calendar\Discord\DiscordSettingsException;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Notifications\Models\NotificationGroup;
use Seat\Notifications\Traits\NotificationDispatchTool;
use Seat\Services\Exceptions\SettingException;

/**
 * Class OperationObserver.
 *
 * @package Seat\Kassie\Calendar\Observers
 */
class OperationObserver
{
    use NotificationDispatchTool;

    /**
     * @param Operation $operation
     */
    public function created(Operation $operation): void
    {
        $this->sendCalendarAlert('seat_calendar_operation_posted', $operation);
        $this->syncWithDiscord("created", $operation);
    }

    private function syncWithDiscord($actionType, $operation): void
    {
        try {
            if (setting('kassie.calendar.discord_integration', true)) {
                (new DiscordAction())
                    ->setType($actionType)
                    ->execute($operation);
            } else {
                logger()->debug("Discord integration is not activated");
            }
        } catch (DiscordActionException|DiscordSettingsException|SettingException $e) {
            logger()->error("Error guild event on discord " . $e->getMessage());
        }
    }

    /**
     * @param Operation $new_operation
     * @throws SettingException
     */
    public function updated(Operation $new_operation): void
    {
        $old_operation = Operation::find($new_operation->id);

        if ($old_operation->is_cancelled != $new_operation->is_cancelled) {
            if ($new_operation->is_cancelled && setting('kassie.calendar.notify_cancel_operation', true)) {
                $this->sendCalendarAlert('seat_calendar_operation_cancelled', $new_operation);
                $this->syncWithDiscord("cancelled", $new_operation);
            } elseif (setting('kassie.calendar.notify_activate_operation', true)) {
                $this->sendCalendarAlert('seat_calendar_operation_activated', $new_operation);
                $this->syncWithDiscord("activated", $new_operation);
            }
        } elseif (
            $new_operation->end_at
            && $new_operation->end_at->lessThan(Carbon::now('UTC'))
            && !$new_operation->is_cancelled
            && setting('kassie.calendar.notify_end_operation', true)
        ) {
            $this->sendCalendarAlert('seat_calendar_operation_ended', $new_operation);
            $this->syncWithDiscord("ended", $new_operation);
        } elseif (setting('kassie.calendar.notify_update_operation', true)) {
            $this->sendCalendarAlert('seat_calendar_operation_updated', $new_operation);
            $this->syncWithDiscord("updated", $new_operation);
        }
    }

    public function sendCalendarAlert($alertType, $operation): void
    {
        $groups = NotificationGroup::with('alerts')
            ->whereHas('alerts', function ($query) use ($alertType): void {
                $query->where('alert', $alertType);
            })->get();

        $this->dispatchNotifications($alertType, $groups, function ($constructor) use ($operation) {
            return new $constructor($operation);
        });
    }

    public function deleted(Operation $operation): void
    {
        $this->syncWithDiscord("deleted", $operation);
    }
}
