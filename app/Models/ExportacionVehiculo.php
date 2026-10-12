<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportacionVehiculo extends Model
{
    protected $table = 'exportacion_vehiculos';

    protected $fillable = [
        'excel_import_version_id',
        'prod_est',
        'cobertura',
        'anio',
        'mes_numero',
        'mes',
        'marca',
        'modelo',
        'tipo',
        'segmento',
        'exportacion',
        'pais_destino',
    ];

    protected $casts = [
        'anio' => 'integer',
        'mes_numero' => 'integer',
        'exportacion' => 'integer',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(
            ExcelImportVersion::class,
            'excel_import_version_id'
        );
    }
}
