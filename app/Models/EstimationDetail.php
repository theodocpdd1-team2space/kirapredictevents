<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimationDetail extends Model
{
    protected $fillable = [
    'estimation_id','equipment_name','unit','quantity','price','total',
    'available','shortage',
    'original_quantity','original_price','original_total',
    'notes','is_custom'
    ];

    protected $casts = [
    'is_custom' => 'boolean',
    ];

    public function estimation()
    {
        return $this->belongsTo(Estimation::class);
    }
}