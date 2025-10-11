<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureIsEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Ambil data fitur dari company user yang login
        $features = auth()->user()->company->features ?? [];

        // Cek apakah fitur yang diminta ada dan nilainya true
        if (!isset($features[$feature]) || $features[$feature] !== true) {
            // Jika tidak aktif, tolak akses
            abort(403, 'This feature is not enabled for your account.');
        }

        return $next($request);
    }
}