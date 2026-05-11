<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
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

    /**
     * User pembuat rule.
     * Pada tabel rules, pembuat rule disimpan pada kolom user_id.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}