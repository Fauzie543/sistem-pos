<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Tenant;

class Company extends Tenant
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'logo',
        'instagram',
        'tiktok',
        'latitude',
        'longitude',
        'wifi_ssid',
        'wifi_password',
        'trial_ends_at',
        'payment_gateway_provider',
        'payment_gateway_keys',
        'payment_gateway_is_production',
        'plan_id',                  // <-- TAMBAHKAN INI
        'subscription_ends_at',
    ];
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'payment_gateway_keys' => 'encrypted:array',
        'payment_gateway_is_production' => 'boolean',
    ];

    public function isCurrent(): bool
    {
        return $this->id === optional(static::current())->id;
    }

    public function getTenantKey(): mixed
    {
        return $this->id;
    }

    public static function findByTenantKey(mixed $key): ?Tenant
    {
        return static::find($key);
    }
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
    public function featureEnabled(string $featureKey): bool
    {
        // Cek 1: Pastikan langganan ada dan masih aktif.
        if (!$this->plan_id || !$this->subscription_ends_at || $this->subscription_ends_at->isPast()) {
            return false;
        }

        // Cek 2: Muat ulang relasi untuk mendapatkan data terbaru.
        $this->load('plan.features');

        // Cek 3: Periksa apakah fitur ada di dalam plan.
        // Penggunaan optional() membuatnya aman bahkan jika relasi plan gagal dimuat.
        return optional($this->plan)->features->contains('key', $featureKey) ?? false;
    }
}