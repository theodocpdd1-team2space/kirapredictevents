<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function estimations()
    {
        return $this->hasMany(Estimation::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function rules()
    {
        return $this->hasMany(Rule::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    public function capacityTiers()
    {
        return $this->hasMany(CapacityTier::class);
    }

    public function equipmentDependencies()
    {
        return $this->hasMany(EquipmentDependency::class);
    }
}