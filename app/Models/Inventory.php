<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'user_id',
        'equipment_name',
        'category',
        'quantity',
        'price',
        'status',
        'image_path',
    ];
}