<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaPublico extends Model
{
    protected $table = 'ventas_publico';

    protected $fillable = [
        'excel_import_version_id',
        'tema',
        'anio',
        'mes',
        'mes_numero',
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
        'seg_pv',
        'origen_2',
    ];

    protected $casts = [
        'anio' => 'integer',
        'mes_numero' => 'integer',
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
