<?php

namespace Seat\Kassie\Calendar\Helpers;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\SlackAttachmentField;
use Seat\Notifications\Models\Integration;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbed;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbedField;
use Seat\Services\Exceptions\SettingException;

/**
 * Class Helper.
 *
 * @package Seat\Kassie\Calendar\Helpers
 */
class Helper
{
    /**
     * @throws SettingException
     */
    public static function BuildFields($op): array
    {
        $fields = [];

        $fields[trans('calendar::seat.starts_at')] = $op->start_at->format('F j @ H:i EVE');
        $fields[trans('calendar::seat.duration')] = $op->getDurationAttribute() ?: trans('calendar::seat.unknown');

        $fields[trans('calendar::seat.importance')] =
            self::ImportanceAsEmoji(
                $op->importance,
                setting('kassie.calendar.slack_emoji_importance_full', true),
                setting('kassie.calendar.slack_emoji_importance_half', true),
                setting('kassie.calendar.slack_emoji_importance_empty', true));

        $fields[trans('calendar::seat.fleet_commander')] = $op->fc ?: trans('calendar::seat.unknown');
        $fields[trans('calendar::seat.staging_system')] = $op->staging_sys ?: trans('calendar::seat.unknown');
        $fields[trans('calendar::seat.staging_info')] = $op->staging_info ?: trans('calendar::seat.unknown');

        return $fields;
    }

    /**
     * @param $op
     * @return Closure
     */
    public static function BuildSlackNotificationAttachment($op): Closure
    {
        $url = url('/calendar/operation', [$op->id]);

        return function ($attachment) use ($op, $url): void {
            $calendarName = trans('calendar::seat.google_calendar');
            $calendarUrl = self::BuildAddToGoogleCalendarUrl($op);

            $attachment->title($op->title, $url)
                ->fields(self::BuildFields($op))
                ->field(function (SlackAttachmentField $field) use ($op, $calendarName, $calendarUrl) {
                    $field
                        ->title(trans('calendar::seat.add_to_calendar'))
                        ->content('<' . $calendarUrl . '|' . $calendarName . '>');
                })
                ->footer(trans('calendar::seat.created_by') . ' ' . $op->user->name)
                ->markdown(['fields']);
        };
    }

    /**
     * @param $op
     * @return Closure
     */
    public static function BuildDiscordOperationEmbed($op): Closure
    {
        $url = url('/calendar/operation', [$op->id]);

        return function (DiscordEmbed $embed) use ($op, $url): void {
            $calendarName = trans('calendar::seat.google_calendar');
            $calendarUrl = self::BuildAddToGoogleCalendarUrl($op);

            $embed->title($op->title, $url)
                ->description($op->description)
                ->fields(self::BuildFields($op))
                ->field(function (DiscordEmbedField $field) use ($op, $calendarName, $calendarUrl): void {
                    $field
                        ->name(trans('calendar::seat.add_to_calendar'))
                        ->value('[' . $calendarName . '](' . $calendarUrl . ')');
                })
                ->author($op->user->name, config('calendar.discord.eve.imageServerUrl') . $op->user->main_character_id . "/portrait")
                ->footer(trans('calendar::seat.created_by') . ' ' . $op->user->name);
        };
    }

    /**
     * @param $importance
     * @param string $emoji_full
     * @param string $emoji_half
     * @param string $emoji_empty
     * @return string
     */
    public static function ImportanceAsEmoji($importance, string $emoji_full, string $emoji_half, string $emoji_empty): string
    {
        $output = "";

        $tmp = explode('.', (string)$importance);
        $val = $tmp[0];
        $dec = 0;

        if (count($tmp) > 1)
            $dec = $tmp[1];

        for ($i = 0; $i < $val; $i++)
            $output .= $emoji_full;

        $left = 5;
        if ($dec != 0) {
            $output .= $emoji_half;
            $left--;
        }

        for ($i = $val; $i < $left; $i++)
            $output .= $emoji_empty;

        return $output;
    }

    /**
     * @param $op
     * @return string
     */
    private static function BuildAddToGoogleCalendarUrl($op): string
    {
        $base_uri = config('calendar.discord.google.url');
        $date_format = 'Ymd\THis\Z';
        if ($op->end_at) {
            $dates = $op->start_at->format($date_format) .
                '/' .
                $op->end_at->format($date_format);
        } else {
            // No event end specified. Google requires one, so set it to the same
            // as the event start
            $dates = $op->start_at->format($date_format) .
                '/' .
                $op->start_at->format($date_format);
        }

        $query = array(
            "action" => "TEMPLATE",
            "text" => $op->title,
            "dates" => $dates,
            "details" => $op->description,
            "location" => $op->staging_sys,
        );
        return $base_uri . '?' . http_build_query($query);
    }

    /**
     * @param array $masks
     * @return int
     */
    public static function arrayBitwiseOr(array $masks): int
    {
        $value = 0;

        foreach ($masks as $mask)
            $value |= $mask;

        return $value;
    }
}
