<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            DB::beginTransaction();

            // 1. Buat perusahaan/tenant baru
            $company = Company::create([
                'name' => $request->company_name,
                // TAMBAHKAN BARIS INI
                'trial_ends_at' => now()->addDays(14), 
            ]);
            
            // Ambil role admin/owner (pastikan role 'admin' ada di seeder Anda)
            $adminRole = Role::where('name', 'admin')->firstOrFail();

            // 2. Buat user pertama sebagai admin/owner untuk perusahaan tersebut
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $adminRole->id,
                'company_id' => $company->id, // <-- Hubungkan user dengan company
            ]);

            DB::commit();

            event(new Registered($user));

            Auth::login($user);

            return redirect(route('dashboard', absolute: false));

        } catch (\Exception $e) {
            DB::rollBack();
            // Optional: Catat error atau tampilkan pesan error yang lebih spesifik
            return redirect()->back()->with('error', 'Gagal mendaftar. Silakan coba lagi.')->withInput();
        }
    }
}