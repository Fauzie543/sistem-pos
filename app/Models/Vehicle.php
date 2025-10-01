<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'license_plate',
        'brand',
        'model',
        'year',
    ];

    /**
     * Get the customer that owns the Vehicle
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}