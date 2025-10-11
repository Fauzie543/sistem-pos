<?php

namespace App\TenantFinder;

use Illuminate\Support\Facades\Auth;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class CurrentUserTenantFinder extends TenantFinder
{
    public function findForRequest($request): ?Tenant
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        // Pastikan user punya relasi ke tenant (misal 'company_id')
        return Tenant::find($user->company_id);
    }
}