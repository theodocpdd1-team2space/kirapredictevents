<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
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

        // ✅ legacy tetap ada (biar aman)
        'duration',

        // ✅ new duration model
        'event_days',
        'hours_per_day',

        'service_level',
        'special_requirement',

        // ✅ crew override
        'crew_operator_qty',
        'crew_engineer_qty',
        'crew_stage_qty',
    ];

    public function estimations()
    {
        return $this->hasMany(\App\Models\Estimation::class);
    }
}