<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreciosOfertas extends Model
{
    protected $table = 'precios_ofertas';

    protected $fillable = [
        'excel_import_version_id',
        'registro_hash',
        'company',
        'model',
        'version',
        'foton',
        'precio',
        'bono',
        'meses_sin_intereses',
        'cargo_por_apertura',
        'tasas_de_interes',
    ];

    protected $casts = [
        'precio' => 'integer',
        'bono' => 'integer',
        'meses_sin_intereses' => 'integer',
        'cargo_por_apertura' => 'decimal:2',
        'tasas_de_interes' => 'decimal:2',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(
            ExcelImportVersion::class,
            'excel_import_version_id'
        );
    }
}
