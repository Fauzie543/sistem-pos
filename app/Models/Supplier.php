<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, UsesTenantConnection;

    protected $fillable = [
        'name',
        'contact_person',
        'phone_number',
        'address',
        'company_id',
        'outlet_id',
    ];
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}