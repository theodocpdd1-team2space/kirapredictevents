<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'profile_photo_path',
        'job_title',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function createdEstimations()
    {
        return $this->hasMany(Estimation::class, 'created_by');
    }

    public function createdInventories()
    {
        return $this->hasMany(Inventory::class, 'created_by');
    }

    public function createdRules()
    {
        return $this->hasMany(Rule::class, 'created_by');
    }

    public function updatedRules()
    {
        return $this->hasMany(Rule::class, 'updated_by');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isActive(): bool
    {
        return !isset($this->status) || $this->status === 'active';
    }

    public function profilePhotoUrl(): string
    {
        return $this->profile_photo_path
            ? asset('storage/' . $this->profile_photo_path)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?? 'User') . '&background=2563eb&color=fff';
    }
}