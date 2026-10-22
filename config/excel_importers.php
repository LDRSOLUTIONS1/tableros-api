<?php

use App\Imports\Handlers\ExportacionImporter;
use App\Imports\Handlers\ProduccionImporter;
use App\Imports\Handlers\VentasLigerosImporter;

return [
    'EXPORTACION'       => ExportacionImporter::class,
    'PRODUCCION'        => ProduccionImporter::class,
    'VENTASLIGEROS'     => VentasLigerosImporter::class,
];
