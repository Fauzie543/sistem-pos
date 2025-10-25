<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\PlanTier;
use App\Models\Company;
use App\Models\Transaction as SubscriptionTransaction;
use Midtrans\Config;
use Midtrans\Snap;
use Spatie\Multitenancy\Landlord;

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
        $plans = Plan::where('is_active', true)->with(['tiers', 'features'])->get();
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
        $transaction = SubscriptionTransaction::create([
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
            'item_details' => [[
                'id'       => $tier->key, // ID unik untuk item, misal: "bengkel_monthly"
                'price'    => $tier->price,
                'quantity' => 1,
                'name'     => 'Langganan ' . $tier->plan->name . ' (' . $tier->duration_months . ' Bulan)',
            ]],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $company->phone ?? '', // Ambil dari data company jika ada
            ],
            'custom_field1' => $tier->id,
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
            
            // ===============================================
            // PERBAIKAN DI SINI: Gunakan Landlord::execute()
            // ===============================================
            Landlord::execute(function () use ($request) {
                // Cari transaksi di database Anda berdasarkan order_id
                $transaction = SubscriptionTransaction::where('order_id', $request->order_id)->first();

                if ($transaction) {
                    // Update status transaksi di database Anda
                    $transaction->update(['status' => $request->transaction_status]);

                    // Jika pembayaran berhasil
                    if ($request->transaction_status == 'settlement' || $request->transaction_status == 'capture') {
                        
                        $tier = $transaction->planTier;
                        $company = $transaction->company;

                        if ($tier && $company) {
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
            });
        }
        
        return response('OK', 200);
    }
}