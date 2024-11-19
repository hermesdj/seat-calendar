<?php

namespace Seat\Kassie\Calendar\database\seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    protected array $schedules = [
        [
            // Remind calendar operations every minute
            'command' => 'calendar:remind',
            'expression' => '* * * * *',
            'allow_overlap' => false,
            'allow_maintenance' => false,
            'ping_before' => null,
            'ping_after' => null,
        ],
        [
            // Sync active operations participation every 15 minutes
            'command' => 'calendar:paps:sync',
            'expression' => '*/15 * * * *',
            'allow_overlap' => false,
            'allow_maintenance' => false,
            'ping_before' => null,
            'ping_after' => null,
        ],
        [
            // Sync discord participation every 2 hours 15 minutes
            'command' => 'calendar:discord:sync',
            'expression' => '15 */2 * * *',
            'allow_overlap' => false,
            'allow_maintenance' => false,
            'ping_before' => null,
            'ping_after' => null,
        ],
    ];

    public function run(): void
    {
        DB::table('schedules')->whereIn('command', [
            'calendar:remind',
            'calendar:paps:sync',
            'calendar:discord:sync',
        ])->delete();

        foreach ($this->schedules as $job) {
            if (DB::table('schedules')->where('command', $job['command'])->exists()) {
                DB::table('schedules')->where('command', $job['command'])->update([
                    'expression' => $job['expression'],
                ]);
            } else {
                DB::table('schedules')->insert($job);
            }
        }
    }
}
