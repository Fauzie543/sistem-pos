<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanTier extends Model
{
    use HasFactory;
    protected $fillable = ['plan_id', 'key', 'price', 'duration_months'];

    public function plan() {
        return $this->belongsTo(Plan::class);
    }
}