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
use Illuminate\Support\Facades\Schema;

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
            

            $defaultOutlet = \App\Models\Outlet::create([
                'company_id' => $company->id,
                'name' => 'Outlet Utama',
                'code' => 'OUTLET_' . strtoupper(substr($company->name, 0, 3)) . '_' . str_pad($company->id, 3, '0', STR_PAD_LEFT),
                'address' => 'Alamat belum diatur',
                'phone' => '-',
            ]);

            if (Schema::hasColumn('users', 'outlet_id')) {
                $user->update(['outlet_id' => $defaultOutlet->id]);
            }

            $cashierRole = Role::where('name', 'kasir')->first();
            if ($cashierRole) {
                User::create([
                    'name' => 'Kasir ' . $company->name,
                    'email' => 'kasir.' . strtolower(str_replace(' ', '', $company->name)) . '@example.com',
                    'password' => Hash::make('kasir123'), // default password bisa ubah di UI nanti
                    'role_id' => $cashierRole->id,
                    'company_id' => $company->id,
                    'outlet_id' => $defaultOutlet->id,
                ]);
            }


            DB::commit();

            event(new Registered($user));

            Auth::login($user);
            session(['active_outlet_id' => $defaultOutlet->id]);

            return redirect(route('dashboard', absolute: false));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Register failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mendaftar. Silakan coba lagi.')->withInput();
        }
    }
}