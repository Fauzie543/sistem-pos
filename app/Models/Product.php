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

    public function activePromo()
    {
        return $this->belongsToMany(Promo::class, 'product_promo')
            ->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->latest()
            ->first();
    }

    public function getFinalPriceAttribute()
    {
        $promo = $this->activePromo();

        if (!$promo) return $this->selling_price;

        if ($promo->type === 'percent') {
            return max(0, $this->selling_price - ($this->selling_price * $promo->value / 100));
        }

        if ($promo->type === 'fixed') {
            return max(0, $this->selling_price - $promo->value);
        }

        return $this->selling_price;
    }
}