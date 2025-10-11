<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = ['key', 'name', 'description', 'features', 'is_active'];
    protected $casts = ['features' => 'array', 'is_active' => 'boolean'];

    public function tiers() {
        return $this->hasMany(PlanTier::class);
    }
}