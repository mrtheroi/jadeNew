<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $source = 'body_hash', int $ttlMinutes = 60): Response
    {
        $idempotencyKey = $this->resolveKey($request, $source);

        if ($idempotencyKey === null) {
            return $next($request);
        }

        $cacheKey = "idempotency:{$source}:{$idempotencyKey}";

        $cachedResponse = Cache::get($cacheKey);

        if ($cachedResponse !== null) {
            Log::info('Idempotency: Returning cached response.', [
                'source' => $source,
                'idempotency_key' => $idempotencyKey,
            ]);

            return new JsonResponse(
                data: $cachedResponse['body'],
                status: $cachedResponse['status'],
                headers: ['X-Idempotent-Replayed' => 'true'],
            );
        }

        $response = $next($request);

        if ($response instanceof JsonResponse && $response->isSuccessful()) {
            Cache::put($cacheKey, [
                'status' => $response->getStatusCode(),
                'body' => $response->getData(true),
            ], now()->addMinutes($ttlMinutes));
        }

        return $response;
    }

    private function resolveKey(Request $request, string $source): ?string
    {
        return match ($source) {
            'body_event_id' => $request->input('eventId') ?? $request->input('event_id'),
            'body_hash' => $this->hashRequestBody($request),
            default => null,
        };
    }

    private function hashRequestBody(Request $request): ?string
    {
        $content = $request->getContent();

        if ($content === '' || $content === '{}') {
            return null;
        }

        return hash('sha256', $content);
    }
}
