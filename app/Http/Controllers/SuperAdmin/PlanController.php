<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use App\Models\Feature;
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
        $features = Feature::all(); // Ambil semua fitur dari database
        return view('superadmin.plans.create', [
            'plan' => new Plan(), // Kirim objek Plan baru yang kosong
            'features' => $features, // Kirim daftar fitur ke view
        ]);
    }
    /**
     * Menyimpan paket baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);

        try {
            DB::beginTransaction();

            $plan = Plan::create($validated);

            // Hubungkan fitur yang dipilih
            if (isset($validated['feature_ids'])) {
                $plan->features()->sync($validated['feature_ids']);
            }

            // Simpan tingkatan harga
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
        $features = Feature::all();
        $plan->load('tiers', 'features');
        return view('superadmin.plans.edit', compact('plan', 'features'));
    }

    /**
     * Memperbarui paket di database.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $this->validatePlan($request, $plan->id);

        try {
            DB::beginTransaction();
            $plan->update($validated);

            if (isset($validated['feature_ids'])) {
                $plan->features()->sync($validated['feature_ids']);
            } else {
                $plan->features()->detach(); // Hapus semua fitur jika tidak ada yang dipilih
            }

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
            'is_active' => 'nullable',
            'tiers' => 'required|array|min:1',
            'tiers.*.duration_months' => 'required|integer|min:1',
            'tiers.*.price' => 'required|integer|min:0',
            'tiers.*.key' => 'required|string',
            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'exists:features,id',
        ]);
    }
}