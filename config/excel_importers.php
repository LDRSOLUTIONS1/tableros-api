<?php

use App\Imports\Handlers\ExportacionImporter;
use App\Imports\Handlers\ProduccionImporter;
use App\Imports\Handlers\VentasLigerosImporter;

use App\Imports\Handlers\EcologicosImporter;
use App\Imports\Handlers\MarcasModelosImporter;
use App\Imports\Handlers\VentasVehiculosImporter;

use App\Imports\Handlers\ComparativosImporter;
use App\Imports\Handlers\ImagenesModelosImporter;
use App\Imports\Handlers\PreciosOfertasImporter;

use App\Imports\Handlers\SegmentoFotonImporter;
use App\Imports\Handlers\Ventas_LigerosImporter;
use App\Imports\Handlers\VentasVehiculosEcologicosImporter;



return [
    'EXPORTACION'                   => ExportacionImporter::class,
    'PRODUCCION'                    => ProduccionImporter::class,
    'VENTASLIGEROS'                 => VentasLigerosImporter::class,

    'ECOLOGICOS'                    => EcologicosImporter::class,
    'MARCASMODELOS'                 => MarcasModelosImporter::class,
    'VENTASVEHICULOS'               => VentasVehiculosImporter::class,

    'COMPARATIVOS'                  => ComparativosImporter::class,
    'IMAGENESMODELOS'               => ImagenesModelosImporter::class,
    'PRECIOSOFERTAS'                => PreciosOfertasImporter::class,

    'SEGMENTOFOTON'                 => SegmentoFotonImporter::class,
    'VENTAS_LIGEROS'                => Ventas_LigerosImporter::class,
    'VENTASVEHICULOSECOLOGICOS'     => VentasVehiculosEcologicosImporter::class,



];
