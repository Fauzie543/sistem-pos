<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Sale extends Model
{
    use HasFactory, SoftDeletes, UsesTenantConnection;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'vehicle_id',
        'mechanic_id',
        'user_id',
        'total_amount',
        'payment_method',
        'status',
        'company_id',
        'outlet_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the mechanic (user) for the Sale
     */
    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    /**
     * Get the cashier (user) for the Sale
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all of the details for the Sale
     */
    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * The products that belong to the Sale.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'sale_details')
            ->withPivot('quantity', 'price', 'subtotal');
    }

    /**
     * The services that belong to the Sale.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'sale_details')
            ->withPivot('quantity', 'price', 'subtotal');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}