<?php

namespace Seat\Kassie\Calendar\Helpers;

use Closure;
use DateTime;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackAttachmentField;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Kassie\Calendar\Exceptions\PapsException;
use Seat\Kassie\Calendar\Jobs\FleetInfoJob;
use Seat\Kassie\Calendar\Jobs\FleetMembersJob;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbed;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbedField;
use Seat\Services\Exceptions\SettingException;

/**
 * Class Helper.
 */
class Helper
{
    /**
     * @throws SettingException
     */
    public static function BuildSlackFields(Operation $op): array
    {
        $fields = [];

        $fields[trans('calendar::seat.starts_at', locale: setting('kassie.calendar.notify_locale'))] = $op->start_at->format('F j @ H:i EVE');
        $fields[trans('calendar::seat.duration', locale: setting('kassie.calendar.notify_locale'))] = $op->getDurationAttribute() ?: trans('calendar::seat.unknown');

        $fields[trans('calendar::seat.importance', locale: setting('kassie.calendar.notify_locale'))] =
            self::ImportanceAsEmoji(
                $op->importance,
                setting('kassie.calendar.slack_emoji_importance_full', true),
                setting('kassie.calendar.slack_emoji_importance_half', true),
                setting('kassie.calendar.slack_emoji_importance_empty', true));

        $fields[trans('calendar::seat.fleet_commander', locale: setting('kassie.calendar.notify_locale'))] = $op->fc ?: trans('calendar::seat.unknown');
        $fields[trans('calendar::seat.staging_system', locale: setting('kassie.calendar.notify_locale'))] = $op->staging_sys ?: trans('calendar::seat.unknown');
        $fields[trans('calendar::seat.staging_info', locale: setting('kassie.calendar.notify_locale'))] = $op->staging_info ?: trans('calendar::seat.unknown');
        $fields[trans('calendar::seat.tags', locale: setting('kassie.calendar.notify_locale'))] = $op->tags->pluck('name')->join(', ') ?: trans('calendar::seat.unknown');

        return $fields;
    }

    /**
     * @throws SettingException
     */
    public static function BuildDiscordFields(Operation $op): array
    {
        $fields = [];

        $currentTime = new DateTime();

        if ($op->start_at > $currentTime) {
            $startTranslation = trans('calendar::seat.starts_at', locale: setting('kassie.calendar.notify_locale'));
        } else {
            $startTranslation = trans('calendar::seat.started_at', locale: setting('kassie.calendar.notify_locale'));
        }

        $fields[] = (new DiscordEmbedField())
            ->name(sprintf(
                '%s (%s)',
                $startTranslation,
                trans('calendar::seat.eve_time', locale: setting('kassie.calendar.notify_locale')),
            ))
            ->value($op->start_at->format('l, j F o @ H:i'))
            ->long()
        ;

        $fields[] = (new DiscordEmbedField())
            ->name(sprintf(
                '%s (%s)',
                $startTranslation,
                trans('calendar::seat.local_time', locale: setting('kassie.calendar.notify_locale')),
            ))
            ->value('<t:'. $op->start_at->getTimestamp() . ':F>')
            ->long()
        ;

        $fields[] = (new DiscordEmbedField())
            ->name(trans('calendar::seat.duration', locale: setting('kassie.calendar.notify_locale')))
            ->value($op->getDurationAttribute() ?: trans('calendar::seat.unknown'))
            ->long()
        ;

        $fields[] = (new DiscordEmbedField())
            ->name(trans('calendar::seat.fleet_commander', locale: setting('kassie.calendar.notify_locale')))
            ->value($op->fc ?: trans('calendar::seat.unknown'))
            ->long()
        ;

        $fields[] = (new DiscordEmbedField())
            ->name(trans('calendar::seat.staging_system', locale: setting('kassie.calendar.notify_locale')))
            ->value($op->staging_sys ?: trans('calendar::seat.unknown'))
        ;

        $fields[] = (new DiscordEmbedField())
            ->name(trans('calendar::seat.staging_info', locale: setting('kassie.calendar.notify_locale')))
            ->value($op->staging_info ?: trans('calendar::seat.unknown'))
        ;

        if (SeatFittingPluginHelper::pluginIsAvailable() && $op->doctrine_id != null) {
            $doctrine = SeatFittingPluginHelper::getOperation($op->doctrine_id);

            if ($doctrine) {
                $doctrineUrl = SeatFittingPluginHelper::generateDoctrineUrl($doctrine->id);

                $fields[] = (new DiscordEmbedField())
                    ->name(trans('calendar::seat.doctrines', locale: setting('kassie.calendar.notify_locale')))
                    ->value('['.$doctrine->name.']('.$doctrineUrl.')')
                    ->long()
                ;
            }
        }

        $fields[] = (new DiscordEmbedField())
            ->name(trans('calendar::seat.importance', locale: setting('kassie.calendar.notify_locale')))
            ->value(
                self::ImportanceAsEmoji(
                    $op->importance,
                    (setting('kassie.calendar.slack_emoji_importance_full', true) ?? ':full_moon:'),
                    (setting('kassie.calendar.slack_emoji_importance_half', true) ?? ':last_quarter_moon:'),
                    (setting('kassie.calendar.slack_emoji_importance_empty', true)) ?? ':new_moon:'),
            )
            ->long()
        ;

        $fields[] = (new DiscordEmbedField())
            ->name(trans('calendar::seat.tags', locale: setting('kassie.calendar.notify_locale')))
            ->value($op->tags->pluck('name')->join(', ') ?: trans('calendar::seat.unknown'))
            ->long()
        ;

        return $fields;
    }

    public static function BuildSlackNotificationAttachment(Operation $op): Closure
    {
        $url = url('/calendar/operation', [$op->id]);

        return function (SlackAttachment $attachment) use ($op, $url): void {
            $calendarName = trans('calendar::seat.google_calendar');
            $calendarUrl = self::BuildAddToGoogleCalendarUrl($op);

            $attachment->title($op->title, $url)
                ->fields(self::BuildSlackFields($op))
                ->field(function (SlackAttachmentField $field) use ($calendarName, $calendarUrl) {
                    $field
                        ->title(trans('calendar::seat.add_to_calendar', locale: setting('kassie.calendar.notify_locale')))
                        ->content('<'.$calendarUrl.'|'.$calendarName.'>');
                })
                ->footer(trans('calendar::seat.created_by').' '.$op->user->name)
                ->markdown(['fields']);
        };
    }

    public static function BuildDiscordOperationEmbed($op): Closure
    {
        $url = url('/calendar/operation', [$op->id]);

        return function (DiscordEmbed $embed) use ($op, $url): void {
            $calendarName = trans('calendar::seat.google_calendar', locale: setting('kassie.calendar.notify_locale'));
            $calendarUrl = self::BuildAddToGoogleCalendarUrl($op);

            $embed->title($op->title, $url)
                ->fields(self::BuildDiscordFields($op))
                ->field(function (DiscordEmbedField $field) use ($calendarName, $calendarUrl): void {
                    $field
                        ->name(trans('calendar::seat.add_to_calendar', locale: setting('kassie.calendar.notify_locale')))
                        ->value('['.$calendarName.']('.$calendarUrl.')');
                })
            ;

            if (is_string($op->description) && strlen($op->description) > 0) {
                $embed->description($op->description);
            }
        };
    }

    public static function ImportanceAsEmoji($importance, string $emoji_full, string $emoji_half, string $emoji_empty): string
    {

        $tmp = explode('.', (string) $importance);
        $val = $tmp[0];
        $dec = 0;

        if (count($tmp) > 1) {
            $dec = $tmp[1];
        }

        $output = str_repeat($emoji_full, $val);

        $left = 5;
        if ($dec != 0) {
            $output .= $emoji_half;
            $left--;
        }

        for ($i = $val; $i < $left; $i++) {
            $output .= $emoji_empty;
        }

        return $output;
    }

    private static function BuildAddToGoogleCalendarUrl($op): string
    {
        $base_uri = config('calendar.discord.google.url');
        $date_format = 'Ymd\THis\Z';
        if ($op->end_at) {
            $dates = $op->start_at->format($date_format).
                '/'.
                $op->end_at->format($date_format);
        } else {
            // No event end specified. Google requires one, so set it to the same
            // as the event start
            $dates = $op->start_at->format($date_format).
                '/'.
                $op->start_at->format($date_format);
        }

        $query = [
            'action' => 'TEMPLATE',
            'text' => $op->title,
            'dates' => $dates,
            'details' => $op->description,
            'location' => $op->staging_sys,
        ];

        return $base_uri.'?'.http_build_query($query);
    }

    public static function arrayBitwiseOr(array $masks): int
    {
        $value = 0;

        foreach ($masks as $mask) {
            $value |= $mask;
        }

        return $value;
    }

    /**
     * @throws PapsException
     */
    public static function syncFleetMembersForPaps(Operation $operation): void
    {
        if (is_null($operation->fc_character_id)) {
            throw new PapsException('No fleet commander has been set for this operation.');
        }

        try {
            $token = RefreshToken::findOrFail($operation->fc_character_id);
        } catch (ModelNotFoundException $e) {
            throw new PapsException('Fleet commander is not already linked to SeAT. Unable to PAP the fleet.');
        }

        FleetInfoJob::dispatchSync($operation->id, $token);
        FleetMembersJob::dispatchSync($operation->id, $token);
    }
}
