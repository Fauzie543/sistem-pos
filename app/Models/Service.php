<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Service extends Model
{
    use HasFactory, SoftDeletes, UsesTenantConnection;

    protected $fillable = ['category_id', 'name', 'price', 'company_id', 'outlet_id',];

    /**
     * Get the category that owns the Service
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}