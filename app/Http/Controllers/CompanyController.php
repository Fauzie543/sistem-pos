<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        // ✅ Ambil data company sesuai company_id user login
        $companyId = auth()->user()->company_id;
        $company = Company::where('id', $companyId)->first();

        return view('company.index', compact('company'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'         => 'required|string|max:255',
            'address'      => 'nullable|string',
            'phone'        => 'nullable|string|max:20',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'instagram'    => 'nullable|string|max:255',
            'tiktok'       => 'nullable|string|max:255',
            'latitude'     => 'nullable|string|max:255',
            'longitude'    => 'nullable|string|max:255',
            'wifi_ssid'    => 'nullable|string|max:255',
            'wifi_password'=> 'nullable|string|max:255',
        ]);

        $companyId = auth()->user()->company_id;

        // ✅ Ambil data perusahaan milik company_id user login
        $company = Company::where('id', $companyId)->first();

        if (!$company) {
            // Kalau belum ada (kasus khusus)
            $company = new Company(['id' => $companyId]);
        }

        // Handle upload logo baru
        if ($request->hasFile('logo')) {
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            $validatedData['logo'] = $request->file('logo')->store('company', 'public');
        }

        $company->fill($validatedData);
        $company->save();

        return redirect()->route('company.index')
            ->with('success', 'Profil perusahaan berhasil diperbarui.');
    }
}