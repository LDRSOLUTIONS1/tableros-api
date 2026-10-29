<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ventas_Ligeros extends Model
{
    protected $table = 'ventas_ligeros';

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
        'origen',
        'pais_origen',
        'cantidad',
        'numero_mes',
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
