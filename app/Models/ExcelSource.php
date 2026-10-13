<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcelSource extends Model
{
    protected $table = 'excel_sources';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'current_version_id',
        'estado',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function versions()
    {
        return $this->hasMany(
            ExcelImportVersion::class,
            'excel_source_id'
        );
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(
            ExcelImportVersion::class,
            'current_version_id'
        );
    }

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [1, 2]);
    }
}
