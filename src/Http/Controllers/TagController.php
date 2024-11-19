<?php

namespace Seat\Kassie\Calendar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Seat\Kassie\Calendar\Models\Tag;
use Seat\Web\Http\Controllers\Controller;

/**
 * Class TagController.
 */
class TagController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required|max:7',
            'bg_color' => [
                'required',
                'regex:^#(?:[0-9a-fA-F]{3}){1,2}$^',
            ],
            'text_color' => [
                'required',
                'regex:^#(?:[0-9a-fA-F]{3}){1,2}$^',
            ],
            'order' => 'required',
            'quantifier' => 'required|numeric',
            'analytics' => 'required|in:strategic,pvp,mining,other,untracked',
            'tag_id' => 'numeric',
        ]);

        $tag = new Tag($request->all());

        if (! is_null($request->input('tag_id'))) {
            $tag = Tag::find($request->input('tag_id'));
            $tag->fill($request->all());
        }

        $tag->save();

        $tag->integrations()->sync($request->integrations);

        return redirect()
            ->back()
            ->with('success', sprintf('The tag "%s" has been successfully created.', $tag->name));
    }

    public function delete(Request $request): RedirectResponse
    {
        $tag = Tag::find($request->tag_id);
        if ($tag != null) {
            Tag::destroy($tag->id);

            return redirect()->back();
        }

        return redirect()->back();
    }

    public function get(int $tag_id): JsonResponse
    {
        $tag = Tag::find($tag_id)->load(['integrations' => function ($query) {
            $query->select('id', 'name');
        }]);

        if (is_null($tag)) {
            return response()->json(['msg' => sprintf('Unable to retrieve tag %s', $tag_id)], 404);
        }

        return response()->json($tag);
    }
}
