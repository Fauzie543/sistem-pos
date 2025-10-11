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
        'features',
        'trial_ends_at',
        'payment_gateway_provider',
        'payment_gateway_keys',
        'payment_gateway_is_production',
    ];
    protected $casts = [
        'features' => 'array',
        'trial_ends_at' => 'datetime',
        'payment_gateway_keys' => 'encrypted:array',
        'payment_gateway_is_production' => 'boolean',
    ];
    public function featureEnabled(string $feature): bool
    {
        return $this->features[$feature] ?? false;
    }

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
}