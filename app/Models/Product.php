<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, UsesTenantConnection;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'description',
        'purchase_price',
        'selling_price',
        'stock',
        'unit',
        'storage_location',
        'company_id',
        'outlet_id',
    ];

    /**
     * Get the category that owns the Product
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    public function promos()
    {
        return $this->belongsToMany(Promo::class, 'product_promo');
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}