<?php

use App\Models\Note;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user only sees their own notes', function () {
    $user = User::factory()->create();
    $ownNote = Note::factory()->for($user)->create();
    $otherNote = Note::factory()->create();

    $this->actingAs($user)
        ->get(route('notes.index'))
        ->assertOk()
        ->assertSee($ownNote->text)
        ->assertDontSee($otherNote->text);
});

test('an authenticated user can create a note', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();

    $this->actingAs($user)
        ->post(route('notes.store'), [
            'text' => 'Ma nouvelle note',
            'tag_id' => $tag->id,
        ])
        ->assertRedirect(route('notes.index'));

    $this->assertDatabaseHas('notes', [
        'user_id' => $user->id,
        'tag_id' => $tag->id,
        'text' => 'Ma nouvelle note',
    ]);
});

test('note creation is validated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('notes.store'), ['text' => '', 'tag_id' => 999])
        ->assertSessionHasErrors(['text', 'tag_id']);
});

test('a user can delete their own note', function () {
    $user = User::factory()->create();
    $note = Note::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('notes.destroy', $note))
        ->assertRedirect(route('notes.index'));

    $this->assertDatabaseMissing('notes', ['id' => $note->id]);
});

test('a user cannot delete another users note', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create();

    $this->actingAs($user)
        ->delete(route('notes.destroy', $note))
        ->assertForbidden();

    $this->assertDatabaseHas('notes', ['id' => $note->id]);
});
