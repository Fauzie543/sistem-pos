<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'phone_number', 'address'];

    /**
     * Get all of the vehicles for the Customer
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}