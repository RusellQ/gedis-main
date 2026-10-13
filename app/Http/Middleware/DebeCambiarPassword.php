<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebeCambiarPassword
{
    /**
     * Si la contraseña sigue siendo temporal, solo se permite
     * la pantalla de cambio y el cierre de sesión.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->debe_cambiar_password) {
            $permitidas = ['password.cambiar', 'password.cambiar.store', 'users.logout'];

            if (! $request->routeIs(...$permitidas)) {
                return redirect()->route('password.cambiar');
            }
        }

        return $next($request);
    }
}
