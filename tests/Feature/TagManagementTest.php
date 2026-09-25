<?php

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an authenticated user can create a tag', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tags.store'), ['name' => 'Laravel'])
        ->assertRedirect(route('tags.index'));

    $this->assertDatabaseHas('tags', ['name' => 'Laravel']);
});

test('tag names must be unique', function () {
    $user = User::factory()->create();
    Tag::factory()->create(['name' => 'Laravel']);

    $this->actingAs($user)
        ->post(route('tags.store'), ['name' => 'Laravel'])
        ->assertSessionHasErrors('name');
});
