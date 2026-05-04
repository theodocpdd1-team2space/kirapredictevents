<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Setting extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'key',
        'value',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getValue(string $key, $default = null, ?int $tenantId = null)
    {
        $tenantId = $tenantId ?? Auth::user()?->tenant_id;

        if (!$tenantId) {
            return $default;
        }

        $val = static::where('tenant_id', $tenantId)
            ->where('key', $key)
            ->value('value');

        return $val ?? $default;
    }

    public static function setValue(string $key, $value, ?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? Auth::user()?->tenant_id;

        if (!$tenantId) {
            return;
        }

        $stored = is_array($value)
            ? json_encode($value)
            : (string) $value;

        static::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'key'       => $key,
            ],
            [
                'user_id' => Auth::id(),
                'value'   => $stored,
            ]
        );
    }

    public static function getUserValue(string $key, $default = null, ?int $userId = null)
    {
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            return $default;
        }

        $val = static::where('user_id', $userId)
            ->where('key', $key)
            ->value('value');

        return $val ?? $default;
    }

    public static function setUserValue(string $key, $value, ?int $userId = null): void
    {
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            return;
        }

        $stored = is_array($value)
            ? json_encode($value)
            : (string) $value;

        static::updateOrCreate(
            [
                'user_id' => $userId,
                'key'     => $key,
            ],
            [
                'tenant_id' => Auth::user()?->tenant_id,
                'value'     => $stored,
            ]
        );
    }
}