<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentasVehiculosEcologicos extends Model
{
    protected $table = 'ventas_vehiculos_ecologicos';

    protected $fillable = [
        'excel_import_version_id',
        'registro_hash',
        'id_origen',
        'tema',
        'anio',
        'mes',
        'numero_mes',
        'entidad_federativa',
        'unidades_vehiculares_electricas',
        'unidades_vehiculares_hibridas_plugin',
        'unidades_vehiculares_hibridas',
    ];

    protected $casts = [
        'id_origen' => 'integer',
        'anio' => 'integer',
        'numero_mes' => 'integer',
        'unidades_vehiculares_electricas' => 'integer',
        'unidades_vehiculares_hibridas_plugin' => 'integer',
        'unidades_vehiculares_hibridas' => 'integer',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(
            ExcelImportVersion::class,
            'excel_import_version_id'
        );
    }
}
