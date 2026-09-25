<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('api routes require authentication', function () {
    $this->getJson('/api/v1/notes')
        ->assertUnauthorized()
        ->assertExactJson([
            'status' => 'error',
            'message' => 'Authentification requise.',
            'data' => null,
        ]);
});

test('a visitor can register and receive an api token', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Lara Croft',
        'email' => 'lara@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'test-suite',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('message', 'Inscription réussie.')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.name', 'Lara Croft')
        ->assertJsonPath('data.user.email', 'lara@example.com')
        ->assertJsonMissingPath('data.user.password')
        ->assertJsonStructure(['status', 'message', 'data' => ['token', 'token_type', 'user']]);

    $this->assertDatabaseHas('users', [
        'name' => 'Lara Croft',
        'email' => 'lara@example.com',
    ]);
    $this->assertCredentials([
        'email' => 'lara@example.com',
        'password' => 'password123',
    ]);
    $this->assertDatabaseCount('personal_access_tokens', 1);
});

test('api registration validates unique email and confirmed password', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $this->postJson('/api/v1/register', [
        'name' => 'Duplicate User',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different-password',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'Les données fournies sont invalides.')
        ->assertJsonStructure(['status', 'message', 'data' => ['errors' => ['email', 'password']]]);
});

test('a user can create an api token', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'test-suite',
    ])
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.email', $user->email)
        ->assertJsonMissingPath('data.user.password')
        ->assertJsonStructure(['status', 'message', 'data' => ['token', 'token_type', 'user']]);

    $this->assertDatabaseCount('personal_access_tokens', 1);
});

test('invalid credentials are rejected', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('data', null);
});

test('logout revokes the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-suite')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/logout')
        ->assertOk()
        ->assertJsonPath('status', 'success');

    $this->assertDatabaseCount('personal_access_tokens', 0);
});
