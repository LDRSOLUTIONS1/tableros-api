<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExcelImportVersion extends Model
{
    protected $table = 'excel_import_versions';

    protected $fillable = [
        'excel_source_id',
        'version_number',
        'original_filename',
        'file_path',
        'file_hash',
        'mime_type',
        'file_size',
        'status',
        'total_rows',
        'imported_by',
        'error_message',
        'imported_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'file_size' => 'integer',
        'total_rows' => 'integer',
        'imported_at' => 'datetime',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(
            ExcelSource::class,
            'excel_source_id'
        );
    }

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'imported_by'
        );
    }

    public function ventasPublico(): HasMany
    {
        return $this->hasMany(
            VentaPublico::class,
            'excel_import_version_id'
        );
    }

    public function produccionVehiculos(): HasMany
    {
        return $this->hasMany(
            ProduccionVehiculo::class,
            'excel_import_version_id'
        );
    }

    public function exportacionVehiculos(): HasMany
    {
        return $this->hasMany(
            ExportacionVehiculo::class,
            'excel_import_version_id'
        );
    }
}
