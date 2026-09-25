<?php

use App\Models\Note;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('an authenticated user can manage the shared tag catalogue', function () {
    Sanctum::actingAs(User::factory()->create());

    $created = $this->postJson('/api/v1/tags', ['name' => 'API'])
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.name', 'API');

    $tagId = $created->json('data.id');

    $this->getJson("/api/v1/tags/{$tagId}")
        ->assertOk()
        ->assertJsonPath('data.id', $tagId)
        ->assertJsonPath('data.name', 'API');

    $this->getJson('/api/v1/tags')
        ->assertOk()
        ->assertJsonFragment(['id' => $tagId, 'name' => 'API']);

    $this->putJson("/api/v1/tags/{$tagId}", ['name' => 'REST'])
        ->assertOk()
        ->assertJsonPath('data.name', 'REST');

    $this->deleteJson("/api/v1/tags/{$tagId}")
        ->assertOk()
        ->assertJsonPath('data', null);

    $this->assertDatabaseMissing('tags', ['id' => $tagId]);
});

test('a tag used by a note cannot be deleted', function () {
    $tag = Tag::factory()->create();
    Note::factory()->for($tag)->create();
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson("/api/v1/tags/{$tag->id}")
        ->assertStatus(409)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('data', null);

    $this->assertDatabaseHas('tags', ['id' => $tag->id]);
});
