<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAccessKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = config('app.api_access_key');
        $incoming = $request->header('X-Access-Key');

        if (!$configured || $incoming !== $configured) {
            return response()->json(['pesan' => 'Access Key tidak valid'], 401);
        }

        return $next($request);
    }
}
