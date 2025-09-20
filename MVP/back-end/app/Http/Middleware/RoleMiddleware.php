<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;


class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user) {
            return response()->json(['error' => 'Token inválido.'], 401);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($user->role !== $role) {
            return response()->json([
                'error' => 'Acesso negado. Permissão insuficiente.'
            ], 403);
        }

        return $next($request);
    }
}