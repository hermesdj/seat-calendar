<?php

namespace Seat\Kassie\Calendar\Observers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Kassie\Calendar\Notifications\OperationActivated;
use Seat\Kassie\Calendar\Notifications\OperationCancelled;
use Seat\Kassie\Calendar\Notifications\OperationEnded;
use Seat\Kassie\Calendar\Notifications\OperationPosted;
use Seat\Kassie\Calendar\Notifications\OperationUpdated;
use Seat\Services\Exceptions\SettingException;

/**
 * Class OperationObserver.
 *
 * @package Seat\Kassie\Calendar\Observers
 */
class OperationObserver
{
    /**
     * @param Operation $operation
     * @throws SettingException
     */
    public function created(Operation $operation): void
    {
        if (setting('kassie.calendar.slack_integration', true) &&
            !is_null($operation->integration) &&
            setting('kassie.calendar.notify_create_operation', true))
            Notification::send($operation, new OperationPosted());
    }

    /**
     * @param Operation $new_operation
     * @throws SettingException
     */
    public function updating(Operation $new_operation): void
    {
        if (setting('kassie.calendar.slack_integration', true) && !is_null($new_operation->integration)) {
            $old_operation = Operation::find($new_operation->id);
            if ($old_operation->is_cancelled != $new_operation->is_cancelled) {
                if ($new_operation->is_cancelled && setting('kassie.calendar.notify_cancel_operation', true))
                    Notification::send($new_operation, new OperationCancelled());
                elseif (setting('kassie.calendar.notify_activate_operation', true))
                    Notification::send($new_operation, new OperationActivated());
            } elseif ($new_operation->end_at &&
                $new_operation->end_at->lessThan(Carbon::now('UTC')) &&
                !$new_operation->is_cancelled &&
                setting('kassie.calendar.notify_end_operation', true)) {
                Notification::send($new_operation, new OperationEnded());
            } elseif (setting('kassie.calendar.notify_update_operation', true)) {
                Notification::send($new_operation, new OperationUpdated());
            }
        }
    }
}
