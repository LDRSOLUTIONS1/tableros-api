<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'collaborator_number',
        'external_rh_id',
        'name',
        'email',
        'brand',
        'location_name',
        'puesto',
        'area',
        'password',
        'remember_token',
        'estado',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }

    public function dashboards(): BelongsToMany
    {
        return $this->belongsToMany(
            PowerBiDashboard::class,
            'dashboard_user',
            'user_id',
            'dashboard_id'
        )->withTimestamps();
    }

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]);
    }
}
