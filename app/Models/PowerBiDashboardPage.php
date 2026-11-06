<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PowerBiDashboardPage extends Model
{
    protected $table = 'power_bi_dashboard_pages';

    protected $fillable = [
        'power_bi_dashboard_id',
        'nombre',
        'descripcion',
        'orden',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'orden'  => 'integer',
            'estado' => 'integer',
        ];
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(
            PowerBiDashboard::class,
            'power_bi_dashboard_id'
        );
    }

    public function excelSources(): BelongsToMany
    {
        return $this->belongsToMany(
            ExcelSource::class,
            'excel_source_dashboard_page',
            'power_bi_dashboard_page_id',
            'excel_source_id'
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

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]);
    }
}
