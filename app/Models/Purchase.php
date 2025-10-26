<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, UsesTenantConnection;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'invoice_number',
        'purchase_date',
        'total_amount',
        'status',
        'company_id',
        'outlet_id',
    ];

    /**
     * Get the supplier that made the Purchase
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user who recorded the Purchase
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all of the details for the Purchase
     */
    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}