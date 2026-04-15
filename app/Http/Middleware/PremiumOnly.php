<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PremiumOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasActivePremium()) {
            return response()->json([
                'message' => 'Pristup je povoleny len pre premium pouzivatelov.'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}