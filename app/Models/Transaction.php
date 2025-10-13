<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = ['company_id', 'plan_tier_id', 'order_id', 'amount', 'status'];

    // Relasi ke model lain
    public function company() { return $this->belongsTo(Company::class); }
    public function planTier() { return $this->belongsTo(PlanTier::class); }
}