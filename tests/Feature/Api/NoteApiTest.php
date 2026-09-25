<?php

use App\Models\Note;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('the notes index only returns the authenticated users notes', function () {
    $user = User::factory()->create();
    $ownNote = Note::factory()->for($user)->create();
    $otherNote = Note::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/notes')
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonFragment(['id' => $ownNote->id, 'text' => $ownNote->text])
        ->assertJsonMissing(['id' => $otherNote->id, 'text' => $otherNote->text]);
});

test('a user can create update and delete a note through the api', function () {
    $user = User::factory()->create();
    $firstTag = Tag::factory()->create();
    $secondTag = Tag::factory()->create();
    Sanctum::actingAs($user);

    $created = $this->postJson('/api/v1/notes', [
        'text' => 'Note API',
        'tag_id' => $firstTag->id,
    ])
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.text', 'Note API')
        ->assertJsonPath('data.tag.id', $firstTag->id);

    $noteId = $created->json('data.id');

    $this->getJson("/api/v1/notes/{$noteId}")
        ->assertOk()
        ->assertJsonPath('data.id', $noteId)
        ->assertJsonPath('data.text', 'Note API');

    $this->putJson("/api/v1/notes/{$noteId}", [
        'text' => 'Note modifiée',
        'tag_id' => $secondTag->id,
    ])
        ->assertOk()
        ->assertJsonPath('data.text', 'Note modifiée')
        ->assertJsonPath('data.tag.id', $secondTag->id);

    $this->deleteJson("/api/v1/notes/{$noteId}")
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data', null);

    $this->assertDatabaseMissing('notes', ['id' => $noteId]);
});

test('note payloads return a consistent validation error', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/notes', ['text' => '', 'tag_id' => 999])
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'Les données fournies sont invalides.')
        ->assertJsonStructure(['status', 'message', 'data' => ['errors' => ['text', 'tag_id']]]);
});

test('a user cannot read update or delete another users note', function () {
    $user = User::factory()->create();
    $note = Note::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson("/api/v1/notes/{$note->id}")
        ->assertForbidden()
        ->assertJsonPath('status', 'error');

    $this->putJson("/api/v1/notes/{$note->id}", [
        'text' => 'Intrusion',
        'tag_id' => $note->tag_id,
    ])
        ->assertForbidden();

    $this->deleteJson("/api/v1/notes/{$note->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('notes', ['id' => $note->id]);
});
