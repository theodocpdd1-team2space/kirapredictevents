<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'tenant_id',
        'created_by',

        'event_name',
        'client_name',
        'client_whatsapp',

        'event_type',
        'event_type_choice',
        'event_type_other',

        'participants',

        'location',
        'location_choice',
        'location_other',

        'venue_type',

        'duration',
        'event_days',
        'hours_per_day',

        'service_level',
        'special_requirement',

        'crew_operator_qty',
        'crew_engineer_qty',
        'crew_stage_qty',
    ];

    protected $casts = [
        'participants' => 'integer',
        'duration' => 'integer',
        'event_days' => 'integer',
        'hours_per_day' => 'integer',
        'crew_operator_qty' => 'integer',
        'crew_engineer_qty' => 'integer',
        'crew_stage_qty' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function estimations()
    {
        return $this->hasMany(Estimation::class);
    }
}