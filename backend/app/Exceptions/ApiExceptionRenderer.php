<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Renders every API exception into one consistent JSON shape:
 * {"message": string, "errors": object|null}.
 */
class ApiExceptionRenderer
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            return self::response($e->getMessage(), 422, $e->errors());
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            return self::response('No autenticado.', 401);
        });

        $exceptions->render(function (JWTException $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            return self::response('Token invalido o expirado.', 401);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            return self::response($e->getMessage() ?: 'No autorizado.', 403);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            return self::response('Recurso no encontrado.', 404);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            return self::response('Recurso no encontrado.', 404);
        });

        $exceptions->render(function (QueryException $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            if ($e->getCode() === '23000') {
                return self::response(
                    'No se puede eliminar: el registro esta en uso por otros datos relacionados.',
                    409,
                );
            }

            return null;
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            return self::response($e->getMessage() ?: 'Error inesperado.', $e->getStatusCode());
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            $message = config('app.debug') ? $e->getMessage() : 'Error interno del servidor.';

            return self::response($message, 500);
        });
    }

    private static function isApi(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    private static function response(string $message, int $status, ?array $errors = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
