<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_photo_path', // kalau nanti dipakai
        'job_title',
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
    public function profilePhotoUrl(): string
    {
    return $this->profile_photo_path
        ? asset('storage/'.$this->profile_photo_path)
        : 'https://ui-avatars.com/api/?name='.urlencode($this->name ?? 'User').'&background=2563eb&color=fff';
    }

}