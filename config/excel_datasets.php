<?php

use App\Models\ExportacionVehiculo;
use App\Models\ProduccionVehiculo;
use App\Models\VentaPublico;

return [
    'EXPORTACION' => [
        'model' => ExportacionVehiculo::class,
        'label' => 'Exportación de vehículos',
    ],

    'PRODUCCION' => [
        'model' => ProduccionVehiculo::class,
        'label' => 'Producción de vehículos',
    ],

    'VENTASLIGEROS' => [
        'model' => VentaPublico::class,
        'label' => 'Ventas al público',
    ],
];
