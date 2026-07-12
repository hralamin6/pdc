<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeSocketId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasHeader('X-Socket-ID')) {
            $socketId = $request->header('X-Socket-ID');
            if ($socketId === 'undefined' || empty($socketId) || ! preg_match('/^\d+\.\d+$/', $socketId)) {
                $request->headers->remove('X-Socket-ID');
            }
        }

        return $next($request);
    }
}
