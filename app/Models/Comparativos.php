<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comparativos extends Model
{
    protected $table = 'comparativos';

    protected $fillable = [
        'excel_import_version_id',
        'registro_hash',
        'codigo',
        'company',
        'model',
        'version',
        'year',
        'price',
        'bonus',
        'price_with_bonus',
        'sales_model_ytd_2025',
        'sales_version_ytd_2025',
        'market_share_by_segment',
        'foton',
        'origin',
        'avaliable',
        'segment',
        'sub_segment',
        'bodywork',
        'drive',
        'energy_system',
        'engine_baterry',
        'displacement',
        'battery_capacity_kw_h',
        'transmission',
        'turbo',
        'autonomy',
        'aceleration_time',
        'maximum_speed',
        'hp',
        'lb_ft',
        'long',
        'tank',
        'payload_capacity',
        'towing_capacity',
        'seats',
        'vol_m3',
        'class',
        'gwv',
        'emission_standard',
        'range',
        'range_precio',
        'charging_time_hrs',
        'towing_capacity_precio',
        'precio_hp',
        'precio_lb_pie',
        'payload_precio',
        'n_airbags',
        'n_adas',
        'engine_brand',
    ];

    protected $casts = [
        'year' => 'integer',
        'price' => 'integer',
        'bonus' => 'integer',
        'price_with_bonus' => 'integer',
        'sales_model_ytd_2025' => 'integer',
        'sales_version_ytd_2025' => 'integer',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(
            ExcelImportVersion::class,
            'excel_import_version_id'
        );
    }
}
