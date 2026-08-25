<?php

declare(strict_types=1);

namespace Liberu\Foundation\ApiAccess\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Liberu\Foundation\ApiAccess\Support\IdempotencyStore;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class ApiContract
{
    public function __construct(private readonly IdempotencyStore $idempotency) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->route() !== null) {
            app(SubstituteBindings::class)->handle($request, static fn (): Response => response('', 204));
        }

        $identity = $this->identity($request);
        $key = $this->key($request);

        if ($key !== null) {
            try {
                $existing = $this->idempotency->begin($identity, $key, $this->requestFingerprint($request));
            } catch (RuntimeException $exception) {
                return $this->problem(409, $exception->getMessage(), 'https://liberu.software/problems/idempotency-conflict');
            }

            if ($existing !== null) {
                if ($existing->response_body === null || $existing->response_status === null) {
                    return $this->problem(409, 'The request is already being processed.', 'https://liberu.software/problems/idempotency-in-progress');
                }

                return response($existing->response_body, (int) $existing->response_status, [
                    'Content-Type' => 'application/json',
                    'Idempotency-Replayed' => 'true',
                ]);
            }
        }

        if (! $this->matchesIfMatch($request)) {
            if ($key !== null) {
                $this->idempotency->forget($identity, $key);
            }

            return $this->problem(412, 'The resource has changed since it was read.', 'https://liberu.software/problems/precondition-failed');
        }

        try {
            $response = $next($request);
        } catch (\Throwable $exception) {
            if ($key !== null) {
                $this->idempotency->forget($identity, $key);
            }

            throw $exception;
        }

        $etag = $this->etag($request, $response);
        if ($etag !== null) {
            $response->headers->set('ETag', $etag);

            if ($request->isMethod('GET') && $this->matchesTag($request->header('If-None-Match'), $etag)) {
                $response = response('', 304, ['ETag' => $etag]);
            }
        }

        if ($key !== null) {
            $body = $response->getContent();
            if ($body === false || $response->getStatusCode() >= 500) {
                $this->idempotency->forget($identity, $key);
            } else {
                $this->idempotency->complete($identity, $key, $response->getStatusCode(), $body);
            }
        }

        return $response;
    }

    private function key(Request $request): ?string
    {
        if (! in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return null;
        }

        $value = $request->header('Idempotency-Key');
        if ($value === null) {
            return null;
        }

        abort_unless(is_string($value) && preg_match('/^[A-Za-z0-9._~-]{1,128}$/', $value) === 1, 422, 'A valid Idempotency-Key header is required.');

        return $value;
    }

    private function identity(Request $request): string
    {
        $actor = $request->user();
        $team = $actor?->currentTeam;

        return hash('sha256', implode('|', [
            (string) ($actor?->getAuthIdentifier() ?? 'guest'),
            (string) ($team?->getKey() ?? 'none'),
            $request->getMethod(),
            $request->path(),
            (string) $request->getQueryString(),
        ]));
    }

    private function requestFingerprint(Request $request): string
    {
        return $request->getContent();
    }

    private function matchesIfMatch(Request $request): bool
    {
        $header = $request->header('If-Match');
        if ($header === null || $request->isMethod('GET')) {
            return true;
        }

        $model = $this->routeModel($request);
        if ($model === null) {
            return false;
        }

        return $this->matchesTag($header, $this->modelEtag($model));
    }

    private function etag(Request $request, Response $response): ?string
    {
        if ($response->getStatusCode() === 204 || $response->getStatusCode() >= 400) {
            return null;
        }

        $model = $this->routeModel($request);
        if ($model !== null) {
            return $this->modelEtag($model);
        }

        $content = $response->getContent();
        if ($content === false || $content === '') {
            return null;
        }

        return '"'.hash('sha256', $content).'"';
    }

    private function routeModel(Request $request): ?Model
    {
        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Model) {
                return $parameter;
            }
        }

        return null;
    }

    private function modelEtag(Model $model): string
    {
        $updatedAt = $model->getAttribute('updated_at');

        return '"'.hash('sha256', implode('|', [
            $model::class,
            (string) $model->getKey(),
            (string) ($updatedAt?->getTimestamp() ?? $updatedAt),
        ])).'"';
    }

    private function matchesTag(?string $header, string $etag): bool
    {
        if ($header === null) {
            return false;
        }

        return trim($header) === '*' || collect(explode(',', $header))->map(static fn (string $tag): string => trim($tag))->contains($etag);
    }

    private function problem(int $status, string $detail, string $type): JsonResponse
    {
        return response()->json([
            'type' => $type,
            'title' => Response::$statusTexts[$status] ?? 'Error',
            'status' => $status,
            'detail' => $detail,
        ], $status, ['Content-Type' => 'application/problem+json']);
    }
}
