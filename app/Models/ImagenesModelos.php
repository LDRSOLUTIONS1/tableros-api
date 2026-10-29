<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagenesModelos extends Model
{
    protected $table = 'imagenes_modelos';

    protected $fillable = [
        'excel_import_version_id',
        'registro_hash',
        'company',
        'model',
        'version',
        'foton',
        'image_url',
        'image_logo',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(
            ExcelImportVersion::class,
            'excel_import_version_id'
        );
    }
}
