<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentDependency extends Model
{
    protected $fillable = [
        'tenant_id',
        'trigger_equipment_name',
        'required_equipment_name',
        'quantity',
        'reason',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}