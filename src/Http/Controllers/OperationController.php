<?php

namespace Seat\Kassie\Calendar\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Seat\Eseye\Exceptions\EsiScopeAccessDeniedException;
use Seat\Eseye\Exceptions\InvalidContainerDataException;
use Seat\Eseye\Exceptions\RequestFailedException;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Kassie\Calendar\Models\Attendee;
use Seat\Kassie\Calendar\Models\Operation;
use Seat\Kassie\Calendar\Models\Pap;
use Seat\Kassie\Calendar\Models\Tag;
use Seat\Notifications\Models\Integration;
use Seat\Services\Contracts\EsiClient;
use Seat\Services\Exceptions\SettingException;
use Seat\Web\Http\Controllers\Controller;
use Seat\Web\Models\Acl\Role;

/**
 * Class OperationController
 * @package Seat\Kassie\Calendar\Http\Controllers
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
     * @param Request $request
     * @return Factory|View
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
            $user_characters = $user_characters->reject(fn($character): bool => $character->character_id == $main_character->character_id);
            $user_characters->prepend($main_character);
        }

        return view('calendar::operation.index', [
            'roles' => $roles,
            'characters' => $user_characters,
            'default_op' => $request->id ?: 0,
            'tags' => $tags,
            'notification_channels' => $notification_channels,
        ]);
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    public function store(Request $request): void
    {
        $this->validate($request, [
            'title' => 'required',
            'importance' => 'required|between:0,5',
            'known_duration' => 'required',
            'time_start' => 'required_without_all:time_start_end|date|after_or_equal:today',
            'time_start_end' => 'required_without_all:time_start'
        ]);

        $operation = new Operation($request->all());
        $tags = [];

        foreach ($request->toArray() as $name => $value) {
            if (empty($value)) {
                $operation->{$name} = null;
            } else if (str_contains($name, 'checkbox-')) {
                $tags[] = $value;
            }
        }

        if ($request->known_duration == "no")
            $operation->start_at = Carbon::parse($request->time_start);
        else {
            $dates = explode(" - ", (string)$request->time_start_end);
            $operation->start_at = Carbon::parse($dates[0]);
            $operation->end_at = Carbon::parse($dates[1]);
        }
        $operation->start_at = Carbon::parse($operation->start_at);

        if ($request->importance == 0)
            $operation->importance = 0;

        $operation->integration_id = ($request->get('integration_id') == "") ?
            null : $request->get('integration_id');

        $operation->user()->associate(auth()->user());

        $operation->save();

        $operation->tags()->attach($tags);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function update(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'title' => 'required',
            'importance' => 'required|between:0,5',
            'known_duration' => 'required',
            'time_start' => 'required_without_all:time_start_end|date|after_or_equal:today',
            'time_start_end' => 'required_without_all:time_start'
        ]);

        $operation = Operation::find($request->operation_id);
        $tags = [];

        if (auth()->user()->can('calendar.update_all') || $operation->user->id == auth()->user()->id) {
            foreach ($request->toArray() as $name => $value) {
                if (empty($value)) {
                    $operation->{$name} = null;
                } else if (str_contains($name, 'checkbox-')) {
                    $tags[] = $value;
                }
            }

            $operation->title = $request->title;
            $operation->role_name = ($request->role_name == "") ? null : $request->role_name;
            $operation->importance = $request->importance;
            $operation->description = $request->description;
            $operation->staging_sys = $request->staging_sys;
            $operation->staging_info = $request->staging_info;
            $operation->staging_sys_id = $request->staging_sys_id == null ? null : $request->staging_sys_id;
            $operation->fc = $request->fc;
            $operation->fc_character_id = $request->fc_character_id == null ? null : $request->fc_character_id;

            if ($request->known_duration == "no") {
                $operation->start_at = Carbon::parse($request->time_start);
                $operation->end_at = null;
            } else {
                $dates = explode(" - ", (string)$request->time_start_end);
                $operation->start_at = Carbon::parse($dates[0]);
                $operation->end_at = Carbon::parse($dates[1]);
            }

            $operation->start_at = Carbon::parse($operation->start_at);

            if ($request->importance == 0)
                $operation->importance = 0;

            $operation->integration_id = ($request->get('integration_id') == "") ?
                null : $request->get('integration_id');

            $operation->save();

            $operation->tags()->sync($tags);

            return redirect()->route('operation.index');
        }

        return redirect()
            ->back()
            ->with('error', 'An error occurred while processing the request.');
    }

    /**
     * @param $operation_id
     * @return JsonResponse|RedirectResponse
     */
    public function find($operation_id): JsonResponse|RedirectResponse
    {
        if (auth()->user()->can('calendar.view')) {
            $operation = Operation::find($operation_id)->load('tags');

            if (!$operation->isUserGranted(auth()->user()))
                return redirect()->back()->with('error', 'You are not granted to this operation !');

            return response()->json($operation);
        }

        return redirect()
            ->back()
            ->with('error', 'An error occurred while processing the request.');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $operation = Operation::find($request->operation_id);

        if ((auth()->user()->can('calendar.delete_all') || $operation->user->id == auth()->user()->id) && $operation != null) {
            if (!$operation->isUserGranted(auth()->user()))
                return redirect()->back()->with('error', 'You are not granted to this operation !');
            Operation::destroy($operation->id);
            return redirect()->route('operation.index');
        }

        return redirect()
            ->back()
            ->with('error', 'An error occurred while processing the request.');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function close(Request $request): RedirectResponse
    {
        $operation = Operation::find($request->operation_id);
        if ((auth()->user()->can('calendar.close_all') || $operation->user->id == auth()->user()->id) && $operation != null) {
            $operation->end_at = Carbon::now('UTC');
            $operation->save();
            return redirect()->route('operation.index');
        }

        return redirect()
            ->back()
            ->with('error', 'An error occurred while processing the request.');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
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

    /**
     * @param Request $request
     * @return RedirectResponse
     */
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
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $operation = Operation::find($request->operation_id);

        if ($operation != null) {

            if (!$operation->isUserGranted(auth()->user()))
                return redirect()->back()->with('error', 'You are not granted to this operation !');

            if ($operation->status == "incoming") {
                Attendee::updateOrCreate(
                    [
                        'operation_id' => $request->operation_id,
                        'character_id' => $request->character_id
                    ],
                    [
                        'user_id' => auth()->user()->id,
                        'status' => $request->status,
                        'comment' => $request->comment
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
     * @param int $operation_id
     * @return RedirectResponse
     * @throws InvalidContainerDataException
     */
    public function paps(int $operation_id): RedirectResponse
    {
        $operation = Operation::find($operation_id);
        if (is_null($operation))
            return redirect()
                ->back()
                ->with('error', 'Unable to retrieve the requested operation.');

        if (!$operation->isUserGranted(auth()->user()))
            return redirect()->back()->with('error', 'You are not granted to this operation !');

        if (is_null($operation->fc_character_id))
            return redirect()
                ->back()
                ->with('error', 'No fleet commander has been set for this operation.');

        if (!in_array($operation->fc_character_id, auth()->user()->associatedCharacterIds()))
            return redirect()
                ->back()
                ->with('error', 'You are not the fleet commander or wrong character has been set.');

        try {
            $token = RefreshToken::findOrFail($operation->fc_character_id);
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->back()
                ->with('error', 'Fleet commander is not already linked to SeAT. Unable to PAP the fleet.');
        }

        $client = $this->eseye($token);

        try {
            $fleet = $client->setVersion('v1')->invoke('get', '/characters/{character_id}/fleet/', [
                'character_id' => $token->character_id,
            ]);

            $membersResponse = $client->setVersion('v1')->invoke('get', '/fleets/{fleet_id}/members/', [
                'fleet_id' => $fleet->getBody()->fleet_id,
            ]);

            foreach ($membersResponse->getBody() as $member) {
                Pap::firstOrCreate([
                    'character_id' => $member->character_id,
                    'operation_id' => $operation_id,
                ], [
                    'ship_type_id' => $member->ship_type_id,
                    'join_time' => carbon($member->join_time)->toDateTimeString(),
                ]);
            }
        } catch (RequestFailedException $e) {
            if ($e->getError() == 'Character is not in a fleet')
                return redirect()
                    ->back()
                    ->with('error', $e->getError());

            if ($e->getError() == 'The fleet does not exist or you don\'t have access to it!')
                return redirect()
                    ->back()
                    ->with('error', sprintf('%s Ensure %s have the fleet boss and try again.', $e->getError(), $operation->fc));

            return redirect()
                ->back()
                ->with('error', 'Esi respond with an unhandled error : (' . $e->getCode() . ') ' . $e->getError());
        } catch (EsiScopeAccessDeniedException $e) {
            return redirect()
                ->back()
                ->with('error', 'Registered tokens has not enough privileges. Please bind your character and pap again.');
        }

        return redirect()
            ->back()
            ->with('success', 'Fleet members has been successfully papped.');
    }

    /**
     * @param RefreshToken $token
     * @return EsiClient
     * @throws InvalidContainerDataException|BindingResolutionException
     */
    private function eseye(RefreshToken $token): EsiClient
    {
        $client = app()->make(EsiClient::class);
        $client->setAuthentication($token);

        return $client;
    }

}
