<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Daftar role yang diizinkan
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Jika user tidak login atau tidak punya role, tolak akses
        if (!auth()->check() || !auth()->user()->role) {
            return redirect('login');
        }

        // Ambil nama role dari user yang sedang login
        $userRole = auth()->user()->role->name;

        // Cek apakah role user ada di dalam daftar role yang diizinkan
        if (in_array($userRole, $roles)) {
            // Jika cocok, izinkan akses ke halaman
            return $next($request);
        }

        // Jika tidak cocok, tolak akses dengan halaman 403 Forbidden
        abort(403, 'ANDA TIDAK MEMILIKI AKSES KE HALAMAN INI.');
    }
}