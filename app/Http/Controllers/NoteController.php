<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Models\Note;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request): View
    {
        return view('notes.index', [
            'notes' => $request->user()->notes()->with('tag')->latest()->get(),
            'tags' => Tag::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $request->user()->notes()->create($request->validated());

        return to_route('notes.index')->with('status', 'Note ajoutée.');
    }

    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return to_route('notes.index')->with('status', 'Note supprimée.');
    }
}
