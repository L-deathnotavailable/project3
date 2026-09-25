<?php

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $isApiRequest = fn (Request $request): bool => $request->is('api/*');

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $isApiRequest($request) || $request->expectsJson()
        );

        $exceptions->render(function (ValidationException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiResponse::error(
                'Les données fournies sont invalides.',
                ['errors' => $exception->errors()],
                422,
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($isApiRequest) {
            return $isApiRequest($request)
                ? ApiResponse::error('Authentification requise.', null, 401)
                : null;
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($isApiRequest) {
            return $isApiRequest($request)
                ? ApiResponse::error('Cette action n’est pas autorisée.', null, 403)
                : null;
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) use ($isApiRequest) {
            return $isApiRequest($request)
                ? ApiResponse::error('Ressource introuvable.', null, 404)
                : null;
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            $message = match ($exception->getStatusCode()) {
                404 => 'Ressource introuvable.',
                405 => 'Méthode HTTP non autorisée.',
                429 => 'Trop de requêtes. Réessayez plus tard.',
                default => $exception->getMessage() ?: 'La requête ne peut pas être traitée.',
            };

            return ApiResponse::error($message, null, $exception->getStatusCode());
        });

        $exceptions->render(function (Throwable $exception, Request $request) use ($isApiRequest) {
            return $isApiRequest($request)
                ? ApiResponse::error('Erreur interne du serveur.', null, 500)
                : null;
        });
    })->create();
