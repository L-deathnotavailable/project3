<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /** @param array{name: string, email: string, password: string} $attributes */
    public function register(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    /** @param array{email: string, password: string} $credentials */
    public function authenticate(array $credentials): ?User
    {
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        return $user;
    }

    /**
     * @return array{
     *     token: string,
     *     token_type: string,
     *     user: array{id: int, name: string, email: string}
     * }
     */
    public function createTokenData(User $user, string $deviceName): array
    {
        $token = $user->createToken($deviceName);

        return [
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
