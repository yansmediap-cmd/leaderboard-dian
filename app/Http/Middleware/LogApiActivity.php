<?php

namespace App\Http\Middleware;

use App\Models\ApiActivityLog;
use App\Models\Dealer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $payload = $request->except(['password', 'password_confirmation']);
        $responsePayload = null;

        if (method_exists($response, 'getContent')) {
            $decoded = json_decode((string) $response->getContent(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $responsePayload = $decoded;
            }
        }

        ApiActivityLog::query()->create([
            'user_id' => $request->user()?->id,
            'dealer_id' => $this->resolveDealerId($payload['kode_dealer'] ?? null),
            'method' => $request->method(),
            'path' => $request->path(),
            'status_code' => $response->getStatusCode(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_payload' => $payload,
            'response_payload' => $responsePayload,
        ]);

        return $response;
    }

    private function resolveDealerId(?string $dealerCode): ?int
    {
        if (! $dealerCode) {
            return null;
        }

        return Dealer::query()
            ->where('kode_dealer', $dealerCode)
            ->value('id');
    }
}
