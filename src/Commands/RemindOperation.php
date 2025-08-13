<?php

namespace Seat\Kassie\Calendar\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Kassie\Calendar\Notifications\NotificationDispatcher;
use Seat\Notifications\Traits\NotificationDispatchTool;
use Seat\Services\Exceptions\SettingException;

/**
 * Class RemindOperation.
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
     *
     * @throws SettingException
     */
    public function handle(): void
    {
        // Ensure we send reminders starting with furthest in the future. That way
        // when more than one event is being reminded, the last reminder in chat
        // is the next event to occur.
        $configured_marks = setting('kassie.calendar.notify_operation_interval', true);
        if ($configured_marks === null) {
            return;
        }
        $marks = explode(',', $configured_marks);
        rsort($marks);

        $allOps = collect();

        logger()->info("Search for operations to remind");

        foreach ($marks as $mark) {
            // This is ran every minutes so it will trigger only when the correct mark is reached for an operation
            $when = Carbon::now('UTC')->floorMinute()->addMinutes($mark);
            $ops = Operation::where('is_cancelled', false)
                ->where('start_at', $when)
                ->get();

            if (!$ops->isEmpty()) {
                foreach ($ops as $op) {
                    if (! $allOps->has($op->id)) {
                        $allOps->put($op->id, $op);
                    }
                }
            }
        }

        if (!$allOps->isEmpty()) {
            logger()->info("Found at least one operation to remind");
            NotificationDispatcher::dispatchOperationsPinged($allOps);
        } else {
            logger()->info("Found no operations to remind");
        }
    }
}
