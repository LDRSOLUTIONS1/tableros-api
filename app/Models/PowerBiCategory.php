<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PowerBiCategory extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function dashboards(): HasMany
    {
        return $this->hasMany(PowerBiDashboard::class, 'category_id');
    }

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]);
    }
}
