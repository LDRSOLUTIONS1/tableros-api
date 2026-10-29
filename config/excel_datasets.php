<?php

use App\Models\ExportacionVehiculo;
use App\Models\ProduccionVehiculo;
use App\Models\VentaPublico;

use App\Models\Ecologicos;
use App\Models\MarcasModelos;
use App\Models\VentasVehiculo;

use App\Models\Comparativos;
use App\Models\ImagenesModelos;
use App\Models\PreciosOfertas;

use App\Models\SegmentoFoton;
use App\Models\Ventas_Ligeros;
use App\Models\VentasVehiculosEcologicos;

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


    'ECOLOGICOS' => [
        'model' => Ecologicos::class,
        'label' => 'Ecológicos',
    ],
    'MARCASMODELOS' => [
        'model' => MarcasModelos::class,
        'label' => 'Marcas y modelos',
    ],
    'VENTASVEHICULOS' => [
        'model' => VentasVehiculo::class,
        'label' => 'Ventas de vehículos',
    ],


    'COMPARATIVOS' => [
        'model' => Comparativos::class,
        'label' => 'Comparativos',
    ],
    'IMAGENESMODELOS' => [
        'model' => ImagenesModelos::class,
        'label' => 'Imágenes de modelos',
    ],
    'PRECIOSOFERTAS' => [
        'model' => PreciosOfertas::class,
        'label' => 'Precios ofertas',
    ],


    'SEGMENTOFOTON' => [
        'model' => SegmentoFoton::class,
        'label' => 'Segmento Foton',
    ],
    'VENTAS_LIGEROS' => [
        'model' => Ventas_Ligeros::class,
        'label' => 'Ventas Ligeros',
    ],
    'VENTASVEHICULOSECOLOGICOS' => [
        'model' => VentasVehiculosEcologicos::class,
        'label' => 'Ventas de Vehículos Ecologicos',
    ],


];
