<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ecologicos extends Model
{
    protected $table = 'ecologicos';

    protected $fillable = [
        'excel_import_version_id',
        'registro_hash',
        'tema',
        'anio',
        'mes',
        'entidad_federativa',
        'unidades_vehiculares_electricas',
        'unidades_vehiculares_hibridas_plugin',
        'unidades_vehiculares_hibridas',
        'numero_mes',
    ];

    protected $casts = [
        'anio' => 'integer',
        'unidades_vehiculares_electricas' => 'integer',
        'unidades_vehiculares_hibridas_plugin' => 'integer',
        'unidades_vehiculares_hibridas' => 'integer',
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
