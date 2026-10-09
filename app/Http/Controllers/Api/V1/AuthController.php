<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register(
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
        $user = $this->authService->authenticate($credentials);

        if (! $user) {
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
        $this->authService->logout($request->user());

        return ApiResponse::success('Déconnexion réussie.');
    }

    private function tokenResponse(
        User $user,
        string $deviceName,
        string $message,
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        return ApiResponse::success(
            $message,
            $this->authService->createTokenData($user, $deviceName),
            $status,
        );
    }
}
