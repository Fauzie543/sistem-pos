<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetActiveOutlet
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Ambil outlet aktif dari session, atau default outlet milik user
            $activeOutletId = session('active_outlet_id', $user->outlet_id);

            // Simpan di konfigurasi global agar bisa diakses di seluruh app
            if ($activeOutletId) {
                config(['app.active_outlet_id' => $activeOutletId]);
            }
        }

        return $next($request);
    }
}