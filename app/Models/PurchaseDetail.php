<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class PurchaseDetail extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'price',
        'subtotal', 
        'company_id',
        'outlet_id',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}