<?php

namespace Seat\Kassie\Calendar\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Notifications\Models\NotificationGroup;
use Seat\Notifications\Traits\NotificationDispatchTool;
use Seat\Services\Exceptions\SettingException;

/**
 * Class RemindOperation.
 *
 * @package Seat\Kassie\Calendar\Commands
 */
class RemindOperation extends Command
{
    use NotificationDispatchTool;

    /**
     * @var string
     */
    protected $signature = 'calendar:remind';

    /**
     * @var string
     */
    protected $description = 'Check for operation to be reminded on Slack';

    /**
     * Process the command.
     * @throws SettingException
     */
    public function handle(): void
    {
        # Ensure we send reminders starting with furthest in the future. That way
        # when more than one event is being reminded, the last reminder in chat
        # is the next event to occur.
        $configured_marks = setting('kassie.calendar.notify_operation_interval', true);
        if ($configured_marks === null) return;
        $marks = explode(',', $configured_marks);
        rsort($marks);

        $allOps = [];

        foreach ($marks as $mark) {
            $when = Carbon::now('UTC')->floorMinute()->addMinutes($mark);
            $ops = Operation::where('is_cancelled', false)
                ->where('start_at', $when)
                ->get();

            if (!$ops->isEmpty()) {
                foreach ($ops as $op) {
                    $allOps[] = $op;
                }
            }
        }

        if (!empty($allOps)) {
            $this->dispatchNotification($allOps);
        }
    }

    private function dispatchNotification(array $ops): void
    {
        $groups = NotificationGroup::with('alerts')
            ->whereHas('alerts', function ($query) {
                $query->where('alert', 'seat_calendar_operation_pinged');
            })->get();

        $this->dispatchNotifications("seat_calendar_operation_pinged", $groups, function ($constructor) use ($ops) {
            return new $constructor($ops);
        });
    }
}
