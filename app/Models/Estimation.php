<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estimation extends Model
{
    protected $fillable = [
        'tenant_id',
        'created_by',

        'event_id',
        'total_cost',
        'status',
        'breakdown',
        'accuracy',
        'is_revised',
        'revision_note',
        'share_token',

        'trace_json',
        'parsed_tags',
    ];

    protected $casts = [
        'total_cost'  => 'integer',
        'breakdown'   => 'array',
        'trace_json'  => 'array',
        'parsed_tags' => 'array',
        'is_revised'  => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function details()
    {
        return $this->hasMany(EstimationDetail::class);
    }

    public function hasShortage(): bool
    {
        return $this->details()->where('shortage', '>', 0)->exists();
    }
}