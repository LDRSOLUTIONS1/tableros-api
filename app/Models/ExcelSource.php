<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExcelSource extends Model
{
    protected $table = 'excel_sources';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'current_version_id',
        'estado',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function dashboardPages(): BelongsToMany
    {
        return $this->belongsToMany(
            PowerBiDashboardPage::class,
            'excel_source_dashboard_page',
            'excel_source_id',
            'power_bi_dashboard_page_id'
        )
            ->withPivot([
                'id',
                'es_requerido',
                'instrucciones',
                'orden',
            ])
            ->withTimestamps()
            ->orderByPivot('orden');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(
            ExcelImportVersion::class,
            'excel_source_id'
        )->orderByDesc('version_number');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(
            ExcelImportVersion::class,
            'current_version_id'
        );
    }

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]);
    }
}
