<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create(
            $request->safe()->only(['name', 'email', 'password'])
        );

        return $this->tokenResponse(
            $user,
            $request->input('device_name', 'api-client'),
            'Inscription réussie.',
            Response::HTTP_CREATED,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return ApiResponse::error('Identifiants invalides.', null, 401);
        }

        return $this->tokenResponse(
            $user,
            $request->input('device_name', 'api-client'),
            'Authentification réussie.',
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return ApiResponse::success('Déconnexion réussie.');
    }

    private function tokenResponse(
        User $user,
        string $deviceName,
        string $message,
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        $token = $user->createToken($deviceName);

        return ApiResponse::success($message, [
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], $status);
    }
}
