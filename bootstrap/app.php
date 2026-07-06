<?php

use App\Http\Middleware\CheckRole;
use App\Http\Responses\ErrorResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        /**
         * Формирует JSON-ошибку для API-запросов, для остальных возвращает null
         * (null означает передачу обработки стандартному механизму Laravel).
         */
        $renderApiError = function (Request $request, string $message, int $status, array $errors = []): ?ErrorResponse {
            return $request->is('api/*')
                ? new ErrorResponse($message, $errors, $status)
                : null;
        };

        $exceptions->render(
            fn (ValidationException $e, Request $request) => $renderApiError($request, 'Переданные данные не корректны.', 422, $e->errors())
        );

        $exceptions->render(
            fn (AuthenticationException $e, Request $request) => $renderApiError($request, 'Запрос требует аутентификации.', 401)
        );

        $exceptions->render(
            fn (AuthorizationException $e, Request $request) => $renderApiError($request, 'Недостаточно прав для выполнения действия.', 403)
        );

        $exceptions->render(
            fn (NotFoundHttpException $e, Request $request) => $renderApiError($request, 'Запрашиваемая страница не существует.', 404)
        );

        $exceptions->render(
            fn (MethodNotAllowedHttpException $e, Request $request) => $renderApiError($request, 'Метод не поддерживается для данного роута.', 405)
        );

        $exceptions->render(
            fn (HttpException $e, Request $request) => $renderApiError($request, $e->getMessage(), $e->getStatusCode())
        );
    })->create();
