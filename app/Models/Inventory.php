<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
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

    /**
     * User pembuat inventory.
     * Pada database VPS, kolom pembuat inventory disimpan sebagai user_id.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}