<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SegmentoFoton extends Model
{
    protected $table = 'segmento_foton';

    protected $fillable = [
        'excel_import_version_id',
        'registro_hash',
        'tema',
        'anio',
        'mes',
        'marca',
        'modelo',
        'tipo',
        'segmento_inegi',
        'segmento_foton',
        'origen',
        'pais_origen',
        'cantidad',
        'numero_mes',
        'marca_mercado_foton',
        'unidad_foton',
    ];

    protected $casts = [
        'anio' => 'integer',
        'cantidad' => 'integer',
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
