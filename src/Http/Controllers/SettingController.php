<?php

namespace Seat\Kassie\Calendar\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Seat\Kassie\Calendar\Http\Validation\SettingsValidation;
use Seat\Kassie\Calendar\Models\Tag;
use Seat\Notifications\Models\Integration;
use Seat\Services\Exceptions\SettingException;
use Seat\Web\Http\Controllers\Controller;

/**
 * Class SettingController.
 *
 * @package Seat\Kassie\Calendar\Http\Controllers
 */
class SettingController extends Controller
{
    /**
     * @return Factory|View
     */
    public function index(): Factory|View
    {
        $tags = Tag::all();
        $notification_channels = Integration::where('type', 'slack')->get();

        return view('calendar::setting.index', [
            'tags' => $tags,
            'slack_integrations' => $notification_channels,
        ]);
    }

    /**
     * @param SettingsValidation $request
     * @return RedirectResponse
     * @throws SettingException
     */
    public function updateSlack(SettingsValidation $request): RedirectResponse
    {
        setting(['kassie.calendar.slack_integration', (bool)$request->slack_integration], true);
        setting(['kassie.calendar.slack_integration_default_channel', $request['slack_integration_default_channel']], true);
        setting(['kassie.calendar.notify_create_operation', (bool)$request->notify_create_operation], true);
        setting(['kassie.calendar.notify_update_operation', (bool)$request->notify_update_operation], true);
        setting(['kassie.calendar.notify_cancel_operation', (bool)$request->notify_cancel_operation], true);
        setting(['kassie.calendar.notify_activate_operation', (bool)$request->notify_activate_operation], true);
        setting(['kassie.calendar.notify_end_operation', (bool)$request->notify_end_operation], true);
        setting(['kassie.calendar.notify_operation_interval', $request['notify_operation_interval']], true);
        setting(['kassie.calendar.slack_emoji_importance_full', $request['slack_emoji_importance_full']], true);
        setting(['kassie.calendar.slack_emoji_importance_half', $request['slack_emoji_importance_half']], true);
        setting(['kassie.calendar.slack_emoji_importance_empty', $request['slack_emoji_importance_empty']], true);

        return redirect()->back();
    }
}
