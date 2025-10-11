<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, UsesTenantConnection;

    protected $fillable = [
        'customer_id',
        'license_plate',
        'brand',
        'model',
        'year',
        'company_id'
    ];

    /**
     * Get the customer that owns the Vehicle
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}