<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    /**
     * Menampilkan halaman pengaturan perusahaan.
     */
    public function index()
    {
        // Ambil data pertama (dan satu-satunya) dari tabel companies
        $company = Company::first();
        return view('company.index', compact('company'));
    }

    /**
     * Menyimpan atau memperbarui data perusahaan.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'instagram' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'latitude' => 'nullable|string|max:255',
            'longitude' => 'nullable|string|max:255',
            'wifi_ssid' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
        ];

        $validatedData = $request->validate($rules);

        // Cari data perusahaan yang ada atau buat instance baru jika tidak ada
        $company = Company::first() ?? new Company();

        // Handle upload logo
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            // Simpan logo baru dan dapatkan path-nya
            $validatedData['logo'] = $request->file('logo')->store('company', 'public');
        }

        // Gunakan updateOrCreate untuk memastikan hanya ada 1 baris data
        // Kita bisa menggunakan ID 1 sebagai acuan
        Company::updateOrCreate(
            ['id' => $company->id ?? 1], // Kondisi pencarian
            $validatedData // Data yang akan di-update atau di-create
        );

        return redirect()->route('company.index')->with('success', 'Company profile updated successfully.');
    }
}