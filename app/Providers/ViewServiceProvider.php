<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Http\View\Composers\CompanyDataComposer;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tambahkan 'layouts.guest' ke dalam array
        View::composer(['layouts.partials.topnav', 'layouts.app', 'layouts.guest'], CompanyDataComposer::class);
    }
}