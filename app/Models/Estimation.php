<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estimation extends Model
{
    protected $fillable = [
        'created_by',
        'event_id',
        'total_cost',
        'status',
        'breakdown',
        'accuracy',
        'is_revised',
        'revision_note',
        'share_token',

        // ✅ penting: biar bisa tersimpan via create()
        'trace_json',
        'parsed_tags',
    ];

    protected $casts = [
        'breakdown'   => 'array',
        'trace_json'  => 'array',
        'parsed_tags' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(\App\Models\Event::class);
    }

    public function details()
    {
        return $this->hasMany(\App\Models\EstimationDetail::class);
    }
}