<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'estado',
    ];
    
    public function modulePermissions()
    {
        return $this->hasMany(RoleModulePermission::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]);
    }
}
