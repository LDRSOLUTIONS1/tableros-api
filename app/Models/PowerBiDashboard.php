<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PowerBiDashboard extends Model
{
    protected $fillable = [
        'category_id',
        'nombre',
        'descripcion',
        'url',
        'fuente',
        'orden',
        'estado',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            PowerBiCategory::class,
            'category_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'dashboard_user',
            'dashboard_id',
            'user_id'
        )->withTimestamps();
    }

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]);
    }
}
