<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarcasModelos extends Model
{
    protected $table = 'marcas_modelos';

    protected $fillable = [
        'excel_import_version_id',
        'registro_hash',
        'tema',
        'anio',
        'mes',
        'marca_im',
        'marca_inegi',
        'mod_im',
        'mod_inegi',
        'version_im',
        'segmento_e',   
        'segmento_b',
        'segmento_c',
        'segmento_inegi',
        'tipo',
        'pais_marca',
        'origen',
        'pais_origen',
        'energia',
        'ventas',
        'columna1',
        'numero_mes',
    ];

    protected $casts = [
        'anio' => 'integer',
        'ventas' => 'integer',
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
