<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a guest is redirected to the legacy login before accessing the frontend transition route', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('an authenticated user is redirected from the legacy dashboard route to React', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(config('frontend.url').'/dashboard');
});
