<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class FeatureManagementController extends Controller
{
    // Definisikan semua fitur yang bisa di-toggle di satu tempat
    private static $availableFeatures = [
        'services' => 'Manajemen Jasa',
        'purchases' => 'Manajemen Pembelian',
        // Tambahkan fitur lain di sini jika ada di masa depan
        // 'reports' => 'Laporan Lanjutan',
    ];

    /**
     * Menampilkan halaman manajemen fitur.
     */
    public function index()
    {
        $companies = Company::all();
        $features = self::$availableFeatures;

        return view('superadmin.features', compact('companies', 'features'));
    }

    /**
     * Memperbarui pengaturan fitur untuk semua perusahaan.
     */
    public function update(Request $request)
    {
        // Ambil semua data 'features' yang dikirim dari form
        $companyFeatures = $request->input('features', []);

        $allCompanies = Company::all();

        foreach ($allCompanies as $company) {
            // Ambil fitur yang dicentang untuk perusahaan ini, defaultnya array kosong
            $submittedFeatures = $companyFeatures[$company->id] ?? [];

            $featuresToSave = [];

            // Loop melalui semua fitur yang tersedia di sistem
            foreach (self::$availableFeatures as $key => $name) {
                // Jika kunci fitur ada di data yang dikirim (dicentang), set nilainya true, jika tidak, false.
                $featuresToSave[$key] = array_key_exists($key, $submittedFeatures);
            }

            // Update kolom 'features' di database
            $company->update(['features' => $featuresToSave]);
        }

        return redirect()->route('superadmin.features.index')->with('success', 'Pengaturan fitur berhasil diperbarui.');
    }
}