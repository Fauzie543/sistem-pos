<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    /**
     * Menampilkan daftar semua paket.
     */
    public function index()
    {
        $plans = Plan::with('tiers')->get();
        return view('superadmin.plans.index', compact('plans'));
    }

    /**
     * Menampilkan form untuk membuat paket baru.
     */
    public function create()
    {
        return view('superadmin.plans.create');
    }

    /**
     * Menyimpan paket baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);

        try {
            DB::beginTransaction();

            // Ubah string fitur menjadi array
            $features = array_filter(array_map('trim', explode("\n", $validated['features_text'])));

            $plan = Plan::create([
                'key' => $validated['key'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'features' => $features,
                'is_active' => $request->has('is_active'),
            ]);

            // Simpan tingkatan harga (tiers)
            foreach ($validated['tiers'] as $tierData) {
                $plan->tiers()->create($tierData);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan paket: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('superadmin.plans.index')->with('success', 'Paket baru berhasil dibuat.');
    }

    /**
     * Menampilkan form untuk mengedit paket.
     */
    public function edit(Plan $plan)
    {
        $plan->load('tiers');
        return view('superadmin.plans.edit', compact('plan'));
    }

    /**
     * Memperbarui paket di database.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $this->validatePlan($request, $plan->id);

        try {
            DB::beginTransaction();

            $features = array_filter(array_map('trim', explode("\n", $validated['features_text'])));

            $plan->update([
                'key' => $validated['key'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'features' => $features,
                'is_active' => $request->has('is_active'),
            ]);

            // Hapus tier lama dan buat ulang (cara paling simpel)
            $plan->tiers()->delete();
            foreach ($validated['tiers'] as $tierData) {
                $plan->tiers()->create($tierData);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui paket: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('superadmin.plans.index')->with('success', 'Paket berhasil diperbarui.');
    }

    /**
     * Menghapus paket.
     */
    public function destroy(Plan $plan)
    {
        $plan->delete(); // Ini akan otomatis menghapus tier-nya juga karena onDelete('cascade')
        return redirect()->route('superadmin.plans.index')->with('success', 'Paket berhasil dihapus.');
    }

    /**
     * Helper untuk validasi.
     */
    private function validatePlan(Request $request, $planId = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'key' => ['required', 'string', 'alpha_dash', Rule::unique('plans')->ignore($planId)],
            'description' => 'nullable|string',
            'features_text' => 'required|string',
            'is_active' => 'nullable',
            'tiers' => 'required|array|min:1',
            'tiers.*.duration_months' => 'required|integer|min:1',
            'tiers.*.price' => 'required|integer|min:0',
            'tiers.*.key' => 'required|string',
        ]);
    }
}