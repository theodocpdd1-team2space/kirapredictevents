<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $fillable = [
        'tenant_id',
        'created_by',
        'updated_by',

        'condition_field',
        'operator',
        'value',
        'action',
        'category',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'action' => 'array',
        'priority' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}