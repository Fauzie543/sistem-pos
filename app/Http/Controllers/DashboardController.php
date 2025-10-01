<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Mengambil data user yang sedang login beserta relasi rolenya
        $user = Auth::user()->load('role');

        // Mengirim data user ke view 'dashboard'
        return view('dashboard', [
            'user' => $user
        ]);
    }
}