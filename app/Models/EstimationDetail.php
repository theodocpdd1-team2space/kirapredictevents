<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimationDetail extends Model
{
    protected $fillable = [
        'estimation_id',
        'equipment_name',
        'unit',
        'quantity',
        'price',
        'total',

        'available',
        'shortage',

        'original_quantity',
        'original_price',
        'original_total',

        'notes',
        'is_custom',

        'is_removed',
        'removed_at',
        'removed_by',
    ];

    protected $casts = [
        'is_custom'  => 'boolean',
        'is_removed' => 'boolean',
        'removed_at' => 'datetime',
    ];

    public function estimation()
    {
        return $this->belongsTo(Estimation::class);
    }

    public function remover()
    {
        return $this->belongsTo(User::class, 'removed_by');
    }
}