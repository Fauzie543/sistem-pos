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

        if (!$company) {
            abort(403, 'Akses ditolak.');
        }

        $isSubscribed = $company->subscription_ends_at && $company->subscription_ends_at->isFuture();
        $onTrial = $company->trial_ends_at && $company->trial_ends_at->isFuture();

        if ($isSubscribed || $onTrial) {
            // Jika salah satu kondisi terpenuhi, izinkan akses
            return $next($request);
        }

        // Jika tidak keduanya, arahkan ke halaman billing
        return redirect()->route('billing.index')->with('error', 'Langganan Anda telah berakhir. Silakan perbarui paket Anda.');
    }
}