<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProduccionVehiculo extends Model
{
    protected $table = 'produccion_vehiculos';

    protected $fillable = [
        'excel_import_version_id',
        'prod_est',
        'cobertura',
        'anio',
        'mes',
        'marca',
        'modelo',
        'tipo',
        'segmento',
        'produccion',
        'numero_mes',
    ];

    protected $casts = [
        'anio' => 'integer',
        'produccion' => 'integer',
        'numero_mes' => 'integer',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(
            ExcelImportVersion::class,
            'excel_import_version_id'
        );
    }
}
