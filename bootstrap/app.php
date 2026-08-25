<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Validation\ValidationException;
use Liberu\Foundation\ApplicationCore\Http\Middleware\SecurityHeaders;
use Liberu\Foundation\Localization\Http\Middleware\SetLocale;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', [SetLocale::class, SecurityHeaders::class, EstablishTeamContext::class]);
        $middleware->priority([
            Authenticate::class,
            EstablishTeamContext::class,
            SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->render(function (Throwable $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $status = match (true) {
                $exception instanceof ValidationException => 422,
                $exception instanceof AuthenticationException => 401,
                $exception instanceof AuthorizationException => 403,
                $exception instanceof ModelNotFoundException => 404,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };
            $title = match ($status) {
                401 => 'Unauthenticated',
                403 => 'Forbidden',
                404 => 'Not Found',
                422 => 'Validation Failed',
                default => $status >= 500 ? 'Server Error' : 'Request Failed',
            };
            $detail = $status >= 500
                ? 'The request could not be completed.'
                : ($exception->getMessage() ?: $title);
            $payload = [
                'type' => 'https://genealogy.liberu.software/problems/'.strtolower(str_replace(' ', '-', $title)),
                'title' => $title,
                'status' => $status,
                'detail' => $detail,
                'instance' => $request->getUri(),
            ];

            if ($exception instanceof ValidationException) {
                $payload['errors'] = $exception->errors();
            }

            return response()->json($payload, $status, ['Content-Type' => 'application/problem+json']);
        });
    })->create();
