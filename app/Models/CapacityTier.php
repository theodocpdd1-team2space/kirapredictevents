<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapacityTier extends Model
{
    protected $fillable = [
        'tenant_id',
        'key',
        'label',
        'min_participants',
        'max_participants',
        'watt_min',
        'watt_max',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'min_participants' => 'integer',
        'max_participants' => 'integer',
        'watt_min' => 'integer',
        'watt_max' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}