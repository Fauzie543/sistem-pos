<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Company;

class CompanyDataComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        // Ambil data perusahaan pertama (dan satu-satunya) dari database
        $company = Company::first();

        // Kirim variabel $company ke view yang menggunakan composer ini
        $view->with('company', $company);
    }
}