<?php

namespace Seat\Kassie\Calendar\database\seeds;

use Illuminate\Database\Seeder;
use Seat\Services\Exceptions\SettingException;

class CalendarSettingsTableSeeder extends Seeder
{
    /**
     * @throws SettingException
     */
    public function run(): void
    {
        if (is_null(setting('kassie.calendar.slack_emoji_importance_full', true))) {
            setting([
                'kassie.calendar.slack_emoji_importance_full',
                ':full_moon_with_face:',
            ], true);
        }

        if (is_null(setting('kassie.calendar.slack_emoji_importance_half', true))) {
            setting([
                'kassie.calendar.slack_emoji_importance_half',
                ':last_quarter_moon:',
            ], true);
        }

        if (is_null(setting('kassie.calendar.slack_emoji_importance_empty', true))) {
            setting([
                'kassie.calendar.slack_emoji_importance_empty',
                ':new_moon_with_face:',
            ], true);
        }
    }
}
