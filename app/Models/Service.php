<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['category_id', 'name', 'price'];

    /**
     * Get the category that owns the Service
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}