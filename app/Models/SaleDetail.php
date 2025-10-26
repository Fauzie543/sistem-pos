<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class SaleDetail extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $fillable = [
        'sale_id',
        'product_id',
        'service_id',
        'quantity',
        'price',
        'subtotal',
        'company_id',
        'outlet_id',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}