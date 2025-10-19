<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureIsEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Panggil method featureEnabled() yang sudah benar logikanya
        if (!auth()->user()->company || !auth()->user()->company->featureEnabled($feature)) {
            abort(403, 'This feature is not enabled for your account.');
        }

        return $next($request);
    }
}