<?php

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class NoteService
{
    /** @return Collection<int, Note> */
    public function listFor(User $user): Collection
    {
        return $user->notes()->with('tag')->latest()->get();
    }

    /** @param array{text: string, tag_id: int} $attributes */
    public function createFor(User $user, array $attributes): Note
    {
        $note = $user->notes()->create($attributes);

        return $note->load('tag');
    }

    /** @param array{text?: string, tag_id?: int} $attributes */
    public function update(Note $note, array $attributes): Note
    {
        $note->update($attributes);

        return $note->load('tag');
    }

    public function delete(Note $note): void
    {
        $note->delete();
    }
}
