<?php

namespace Seat\Kassie\Calendar\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Seat\Eseye\Exceptions\InvalidContainerDataException;
use Seat\Kassie\Calendar\Discord\DiscordAction;
use Seat\Kassie\Calendar\Exceptions\PapsException;
use Seat\Kassie\Calendar\Helpers\Helper;
use Seat\Kassie\Calendar\Helpers\SeatFittingPluginHelper;
use Seat\Kassie\Calendar\Models\Attendee;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Kassie\Calendar\Models\Tag;
use Seat\Kassie\Calendar\Notifications\NotificationDispatcher;
use Seat\Notifications\Models\Integration;
use Seat\Services\Exceptions\SettingException;
use Seat\Web\Http\Controllers\Controller;
use Seat\Web\Models\Acl\Role;

/**
 * Class OperationController
 */
class OperationController extends Controller
{
    /**
     * OperationController constructor.
     */
    public function __construct()
    {
        $this->middleware('can:calendar.view')->only('index');
        $this->middleware('can:calendar.create')->only('store');
    }

    /**
     * @throws SettingException
     */
    public function index(Request $request): Factory|View
    {
        $notification_channels = Integration::where('type', 'slack')->get();

        $tags = Tag::all()->sortBy('order');

        $roles = Role::orderBy('title')->get();
        $user_characters = auth()->user()->characters->sortBy('name');
        $main_character = auth()->user()->main_character;

        if ($main_character != null) {
            $main_character->main = true;
            $user_characters = $user_characters->reject(fn ($character): bool => $character->character_id == $main_character->character_id);
            $user_characters->prepend($main_character);
        }

        $doctrines = [];

        if (SeatFittingPluginHelper::pluginIsAvailable()) {
            $doctrines = SeatFittingPluginHelper::listDoctrines();
        }

        return view('calendar::operation.index', [
            'roles' => $roles,
            'characters' => $user_characters,
            'default_op' => $request->id ?: 0,
            'tags' => $tags,
            'notification_channels' => $notification_channels,
            'doctrines' => $doctrines,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): void
    {
        $this->validate($request, [
            'title' => 'required',
            'importance' => 'required|between:0,5',
            'known_duration' => 'required',
            'time_start' => 'required_without_all:time_start_end|date|after_or_equal:today',
            'time_start_end' => 'required_without_all:time_start',
            'doctrine_id' => 'integer|nullable',
        ]);

        $operation = new Operation($request->all());
        $tags = [];

        foreach ($request->toArray() as $name => $value) {
            if (empty($value)) {
                $operation->{$name} = null;
            } elseif (str_contains($name, 'checkbox-')) {
                $tags[] = $value;
            }
        }

        if ($request->known_duration == 'no') {
            $operation->start_at = Carbon::parse($request->time_start);
        } else {
            $dates = explode(' - ', (string) $request->time_start_end);
            $operation->start_at = Carbon::parse($dates[0]);
            $operation->end_at = Carbon::parse($dates[1]);
        }
        $operation->start_at = Carbon::parse($operation->start_at);

        if ($request->importance == 0) {
            $operation->importance = 0;
        }

        $operation->integration_id = ($request->get('integration_id') == '') ?
            null : $request->get('integration_id');

        $operation->doctrine_id = ($request->get('doctrine_id') == '') ? null : $request->get('doctrine_id');

        $operation->user()->associate(auth()->user());

        $operation->save();

        $operation->tags()->attach($tags);

        NotificationDispatcher::dispatchOperationCreated($operation);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'title' => 'required',
            'importance' => 'required|between:0,5',
            'known_duration' => 'required',
            'time_start' => 'required_without_all:time_start_end|date|after_or_equal:today',
            'time_start_end' => 'required_without_all:time_start',
        ]);

        $operation = Operation::find($request->operation_id);
        $tags = [];

        if (auth()->user()->can('calendar.update_all') || $operation->user->id == auth()->user()->id) {
            foreach ($request->toArray() as $name => $value) {
                if (empty($value)) {
                    $operation->{$name} = null;
                } elseif (str_contains($name, 'checkbox-')) {
                    $tags[] = $value;
                }
            }

            $operation->title = $request->title;
            $operation->role_name = ($request->role_name == '') ? null : $request->role_name;
            $operation->importance = $request->importance;
            $operation->description = $request->description;
            $operation->staging_sys = $request->staging_sys;
            $operation->staging_info = $request->staging_info;
            $operation->staging_sys_id = $request->staging_sys_id == null ? null : $request->staging_sys_id;
            $operation->fc = $request->fc;
            $operation->fc_character_id = $request->fc_character_id == null ? null : $request->fc_character_id;

            if ($request->known_duration == 'no') {
                $operation->start_at = Carbon::parse($request->time_start);
                $operation->end_at = null;
            } else {
                $dates = explode(' - ', (string) $request->time_start_end);
                $operation->start_at = Carbon::parse($dates[0]);
                $operation->end_at = Carbon::parse($dates[1]);
            }

            $operation->start_at = Carbon::parse($operation->start_at);

            if ($request->importance == 0) {
                $operation->importance = 0;
            }

            $operation->integration_id = ($request->get('integration_id') == '') ?
                null : $request->get('integration_id');

            $operation->save();

            $operation->tags()->sync($tags);

            NotificationDispatcher::dispatchOperationUpdated($operation);
            DiscordAction::syncWithDiscord('updated', $operation);

            return redirect()->route('operation.index');
        }

        return redirect()
            ->back()
            ->with('error', 'An error occurred while processing the request.');
    }

    public function find($operation_id): JsonResponse|RedirectResponse
    {
        if (auth()->user()->can('calendar.view')) {
            $operation = Operation::find($operation_id)->load('tags');

            if (! $operation->isUserGranted(auth()->user())) {
                return redirect()->back()->with('error', 'You are not granted to this operation !');
            }

            return response()->json($operation);
        }

        return redirect()
            ->back()
            ->with('error', 'An error occurred while processing the request.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $operation = Operation::find($request->operation_id);

        if ((auth()->user()->can('calendar.delete_all') || $operation->user->id == auth()->user()->id) && $operation != null) {
            if (! $operation->isUserGranted(auth()->user())) {
                return redirect()->back()->with('error', 'You are not granted to this operation !');
            }
            Operation::destroy($operation->id);

            return redirect()->route('operation.index');
        }

        return redirect()
            ->back()
            ->with('error', 'An error occurred while processing the request.');
    }

    public function close(Request $request): RedirectResponse
    {
        $operation = Operation::find($request->operation_id);
        if ((auth()->user()->can('calendar.close_all') || $operation->user->id == auth()->user()->id) && $operation != null) {
            $operation->end_at = Carbon::now('UTC');
            $operation->save();
            NotificationDispatcher::dispatchOperationEnded($operation);

            return redirect()->route('operation.index');
        }

        return redirect()
            ->back()
            ->with('error', 'An error occurred while processing the request.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $operation = Operation::find($request->operation_id);
        if ((auth()->user()->can('calendar.close_all') || $operation->user->id == auth()->user()->id) && $operation != null) {
            $this->changeStatus($operation, true);

            return redirect()->route('operation.index');
        }

        return redirect()
            ->back()
            ->with('error', 'An error occurred while processing the request.');
    }

    public function activate(Request $request): RedirectResponse
    {
        $operation = Operation::find($request->operation_id);
        if ((auth()->user()->can('calendar.close_all') || $operation->user->id == auth()->user()->id) && $operation != null) {
            $this->changeStatus($operation, false);

            return redirect()->route('operation.index');
        }

        return redirect()
            ->back()
            ->with('error', 'An error occurred while processing the request.');
    }

    private function changeStatus(Operation $operation, bool $status): void
    {
        $operation->timestamps = false;
        $operation->is_cancelled = $status;
        $operation->save();

        if ($status) {
            NotificationDispatcher::dispatchOperationCancelled($operation);
            DiscordAction::syncWithDiscord('cancelled', $operation);
        } else {
            NotificationDispatcher::dispatchOperationActivated($operation);
            DiscordAction::syncWithDiscord('activated', $operation);
        }
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $operation = Operation::find($request->operation_id);

        if ($operation != null) {

            if (! $operation->isUserGranted(auth()->user())) {
                return redirect()->back()->with('error', 'You are not granted to this operation !');
            }

            if ($operation->status == 'incoming') {
                Attendee::updateOrCreate(
                    [
                        'operation_id' => $request->operation_id,
                        'character_id' => $request->character_id,
                    ],
                    [
                        'user_id' => auth()->user()->id,
                        'status' => $request->status,
                        'comment' => $request->comment,
                    ]
                );

                return redirect()->route('operation.index');
            }
        }

        return redirect()
            ->back()
            ->with('error', 'An error occurred while processing the request.');
    }

    /**
     * @throws InvalidContainerDataException
     */
    public function paps(int $operation_id): RedirectResponse
    {
        $operation = Operation::find($operation_id);
        if (is_null($operation)) {
            return redirect()
                ->back()
                ->with('error', 'Unable to retrieve the requested operation.');
        }

        if (! $operation->isUserGranted(auth()->user())) {
            return redirect()->back()->with('error', 'You are not granted to this operation !');
        }

        if (is_null($operation->fc_character_id)) {
            return redirect()
                ->back()
                ->with('error', 'No fleet commander has been set for this operation.');
        }

        if (! in_array($operation->fc_character_id, auth()->user()->associatedCharacterIds())) {
            return redirect()
                ->back()
                ->with('error', 'You are not the fleet commander or wrong character has been set.');
        }

        try {
            Helper::syncFleetMembersForPaps($operation);
        } catch (PapsException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', 'Fleet members has been successfully papped.');
    }
}
