<?php

namespace App\Http\Middleware;

use App\Models\Dealer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class EnsureDealerIpWhitelisted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local', 'testing') && config('leaderboard.security.skip_ip_whitelist_in_local', true)) {
            return $next($request);
        }

        $dealerCode = $request->input('kode_dealer');
        if (! $dealerCode) {
            abort(422, 'kode_dealer is required.');
        }

        $dealer = Dealer::query()
            ->where('kode_dealer', $dealerCode)
            ->first();

        if (! $dealer) {
            abort(404, 'Dealer not found.');
        }

        $ip = $request->ip();

        $allowed = $dealer->apiWhitelists()
            ->where('is_active', true)
            ->pluck('ip_address')
            ->contains(fn (string $allowedIp) => IpUtils::checkIp($ip, $allowedIp));

        if (! $allowed) {
            abort(403, 'IP address is not whitelisted.');
        }

        return $next($request);
    }
}
