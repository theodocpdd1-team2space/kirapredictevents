<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $fillable = [
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
        // simpan action sebagai JSON di DB, dipakai sebagai array di PHP
        'action' => 'array',
    ];
}