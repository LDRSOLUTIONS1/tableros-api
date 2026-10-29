<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentasVehiculo extends Model
{
    protected $table = 'ventas_vehiculos';

    protected $fillable = [
        'excel_import_version_id',
        'registro_hash',
        'tema',
        'anio',
        'mes',
        'numero_mes',
        'marca',
        'modelo',
        'tipo',
        'segmento_inegi',
        'origen',
        'pais_origen',
        'cantidad',
        'mercado_inegi',
        'marca_mercado_foton',
        'unidad_foton_inegi',
        'seg_p_v',
    ];

    protected $casts = [
        'anio' => 'integer',
        'numero_mes' => 'integer',
        'cantidad' => 'integer',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(
            ExcelImportVersion::class,
            'excel_import_version_id'
        );
    }
}
