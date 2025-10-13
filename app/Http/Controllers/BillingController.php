<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\PlanTier;
use App\Models\Company;
use Midtrans\Config;
use Midtrans\Snap;

class BillingController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }
    
    public function index()
    {
        $plans = Plan::where('is_active', true)->with('tiers')->get();
        return view('billing.index', compact('plans'));
    }

    // Nanti, kita akan buat logika untuk memproses pembayaran di sini
    public function processSubscription(Request $request)
    {
        $request->validate(['plan_tier_id' => 'required|exists:plan_tiers,id']);

        $user = auth()->user();
        $company = $user->company;
        $tier = PlanTier::with('plan')->find($request->plan_tier_id);

        // 1. Buat record transaksi dengan status 'pending'
        $transaction = Transaction::create([
            'company_id' => $company->id,
            'plan_tier_id' => $tier->id,
            'order_id' => 'SUB-' . $company->id . '-' . time(),
            'amount' => $tier->price,
            'status' => 'pending',
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $transaction->order_id, // Gunakan order_id dari database
                'gross_amount' => $tier->price,
            ],
            'item_details' => [[ /* ... (tidak berubah) ... */ ]],
            'customer_details' => [ /* ... (tidak berubah) ... */ ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            // Kirim juga order_id ke frontend
            return response()->json(['snap_token' => $snapToken, 'order_id' => $transaction->order_id]);
        } catch (\Exception $e) {
            // Jika gagal, hapus record transaksi yang baru dibuat
            $transaction->delete();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menangani notifikasi dari Midtrans (Webhook).
     */
    public function handleNotification(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed == $request->signature_key) {
            if ($request->transaction_status == 'settlement' || $request->transaction_status == 'capture') {
                
                // Ambil tier_id dari metadata yang kita kirim sebelumnya
                $tier_id = $request->custom_field1;
                $tier = PlanTier::find($tier_id);

                // Cari company_id dari order_id
                $orderParts = explode('-', $request->order_id);
                $companyId = $orderParts[1] ?? null;

                if ($tier && $companyId) {
                    $company = Company::find($companyId);
                    if ($company) {
                         // Hitung tanggal akhir langganan
                        $subscriptionEndDate = now()->addMonths($tier->duration_months);

                        // Update data perusahaan
                        $company->update([
                            'plan_id' => $tier->plan_id,
                            'subscription_ends_at' => $subscriptionEndDate,
                            'trial_ends_at' => null, // Matikan masa trial
                        ]);
                    }
                }
            }
        }
        
        return response('OK', 200);
    }
}