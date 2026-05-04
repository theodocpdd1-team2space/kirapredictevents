<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'tenant_id',
        'created_by',

        'equipment_name',
        'category',
        'quantity',
        'price',
        'status',
        'image_path',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}