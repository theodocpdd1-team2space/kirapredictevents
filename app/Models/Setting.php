<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Setting extends Model
{
    protected $fillable = ['user_id', 'key', 'value'];

    public static function getValue(string $key, $default = null, ?int $userId = null)
    {
        $userId = $userId ?? Auth::id();
        if (!$userId) return $default;

        $val = static::where('user_id', $userId)
            ->where('key', $key)
            ->value('value');

        return $val ?? $default;
    }

    public static function setValue(string $key, $value, ?int $userId = null): void
    {
        $userId = $userId ?? Auth::id();
        if (!$userId) return;

        // ✅ kalau array -> simpan JSON string
        $stored = is_array($value) ? json_encode($value) : (string) $value;

        static::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $stored]
        );
    }
}