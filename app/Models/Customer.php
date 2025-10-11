<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes, UsesTenantConnection;

    protected $fillable = ['name', 'phone_number', 'address', 'company_id'];

    /**
     * Get all of the vehicles for the Customer
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}