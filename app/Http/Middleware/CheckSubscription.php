<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Izinkan Super Admin untuk mengakses segalanya
        if ($user->role->name === 'superadmin') {
            return $next($request);
        }

        $company = $user->company;

        // Cek jika user tidak punya company ATAU masa trial sudah lewat
        if (!$company || ($company->trial_ends_at && $company->trial_ends_at->isPast())) {
            // Arahkan ke halaman pembayaran (yang akan kita buat)
            return redirect()->route('billing.index')->with('error', 'Masa uji coba Anda telah berakhir. Silakan berlangganan untuk melanjutkan.');
        }

        // Jika semua aman (masih dalam masa trial), izinkan akses
        return $next($request);
    }
}