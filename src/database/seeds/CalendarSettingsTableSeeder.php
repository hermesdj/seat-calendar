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

        if (is_null(setting('kassie.calendar.default_known_duration', true))) {
            setting([
                'kassie.calendar.default_known_duration',
                0
            ], true);
        }

        if (is_null(setting('kassie.calendar.default_op_duration', true))) {
            setting([
                'kassie.calendar.default_op_duration',
                0
            ], true);
        }

        if (is_null(setting('kassie.calendar.allow_multiple_tags', true))) {
            setting([
                'kassie.calendar.forbid_multiple_tags',
                0
            ], true);
        }
    }
}
