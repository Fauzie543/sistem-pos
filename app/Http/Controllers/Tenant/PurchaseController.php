<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function index()
    {
        return view('purchases.index');
    }

    public function data()
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id'); // ✅ outlet aktif

        $purchases = Purchase::with(['supplier', 'user'])
            ->where('company_id', $companyId)
            ->where('outlet_id', $outletId) // ✅ filter outlet
            ->select('purchases.*');

        return DataTables::of($purchases)
            ->addIndexColumn()
            ->editColumn('purchase_date', fn($p) => Carbon::parse($p->purchase_date)->format('d F Y'))
            ->editColumn('total_amount', fn($p) => 'Rp ' . number_format($p->total_amount, 0, ',', '.'))
            ->addColumn('action', function ($purchase) {
                return '<a href="'.route('purchases.show', $purchase->id).'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs">View</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id');

        // ✅ Supplier per outlet
        $suppliers = Supplier::where('company_id', $companyId)
            ->where(function ($q) use ($outletId) {
                $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
            })
            ->orderBy('name')
            ->get();

        return view('purchases.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id'); // ✅ outlet aktif

        $request->validate([
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')
                    ->where('company_id', $companyId)
                    ->where(function ($q) use ($outletId) {
                        $q->where('outlet_id', $outletId)->orWhereNull('outlet_id');
                    }),
            ],
            'purchase_date' => ['required', 'date'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => [
                'required',
                Rule::exists('products', 'id')
                    ->where('company_id', $companyId)
                    ->where('outlet_id', $outletId),
            ],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = collect($request->products)
                ->sum(fn($p) => $p['quantity'] * $p['price']);

            $purchase = Purchase::create([
                'supplier_id'    => $request->supplier_id,
                'user_id'        => Auth::id(),
                'invoice_number' => $request->invoice_number,
                'purchase_date'  => $request->purchase_date,
                'total_amount'   => $totalAmount,
                'status'         => 'diterima',
                'company_id'     => $companyId,
                'outlet_id'      => $outletId, // ✅ outlet aktif
            ]);

            foreach ($request->products as $p) {
                $purchase->details()->create([
                    'product_id'  => $p['id'],
                    'quantity'    => $p['quantity'],
                    'price'       => $p['price'],
                    'subtotal'    => $p['quantity'] * $p['price'],
                    'company_id'  => $companyId,
                    'outlet_id'   => $outletId, // ✅
                ]);

                // ✅ Update stok hanya di outlet ini
                $product = Product::where('company_id', $companyId)
                    ->where('outlet_id', $outletId)
                    ->find($p['id']);

                if ($product) {
                    $product->increment('stock', $p['quantity']);
                }
            }

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase created successfully for current outlet.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: '.$e->getMessage())->withInput();
        }
    }

    public function show(Purchase $purchase)
    {
        // 🔒 Pastikan hanya outlet aktif yang bisa melihat
        $outletId = config('app.active_outlet_id');
        if ($purchase->outlet_id !== $outletId) {
            abort(403, 'Tidak dapat melihat pembelian dari outlet lain.');
        }

        $purchase->load(['supplier', 'user', 'details.product']);
        return view('purchases.show', compact('purchase'));
    }

    public function searchProducts(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id');
        $term      = $request->input('term');

        $products = Product::where('company_id', $companyId)
            ->where('outlet_id', $outletId)
            ->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('sku', 'LIKE', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'sku', 'purchase_price']);

        return response()->json($products);
    }
}