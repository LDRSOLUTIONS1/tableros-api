<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExcelSourcesSeeder extends Seeder
{
    public function run(): void
    {
        $registros = [
            // TABLERO: VENTAS DE VEHÍCULOS LIGEROS
            [
                'codigo' => 'EXPORTACION',
                'nombre' => 'Exportación de vehículos',
                'descripcion' => 'Archivo de exportación utilizado en la página Producción y Expo.',
            ],
            [
                'codigo' => 'PRODUCCION',
                'nombre' => 'Producción de vehículos',
                'descripcion' => 'Archivo de producción utilizado en la página Producción y Expo.',
            ],
            [
                'codigo' => 'VENTASLIGEROS',
                'nombre' => 'Ventas de vehículos ligeros',
                'descripcion' => 'Archivo comercial utilizado en las páginas de ventas y análisis comercial.',
            ],

            // TABLERO: VEHÍCULOS HÍBRIDOS Y ELÉCTRICOS
            [
                'codigo' => 'ECOLOGICOS',
                'nombre' => 'Vehículos ecológicos',
                'descripcion' => 'Archivo utilizado en las páginas Ventas, Histórico y Regiones.',
            ],
            [
                'codigo' => 'MARCASMODELOS',
                'nombre' => 'Marcas y modelos',
                'descripcion' => 'Archivo utilizado en la página Modelos.',
            ],
            [
                'codigo' => 'VENTASVEHICULOS',
                'nombre' => 'Ventas de vehículos',
                'descripcion' => 'Archivo complementario utilizado en la página Ventas.',
            ],

            // TABLERO: VEHÍCULOS LIGEROS Y COMPARATIVOS
            [
                'codigo' => 'COMPARATIVOS',
                'nombre' => 'Comparativos',
                'descripcion' => 'Especificaciones técnicas y características de modelos y marcas competidoras.',
            ],
            [
                'codigo' => 'IMAGENESMODELOS',
                'nombre' => 'Imágenes de modelos',
                'descripcion' => 'Fotografías de vehículos y logotipos de marcas y modelos.',
            ],
            [
                'codigo' => 'PRECIOSOFERTAS',
                'nombre' => 'Precios y ofertas',
                'descripcion' => 'Precios de lista, descuentos y condiciones comerciales.',
            ],
            [
                'codigo' => 'SEGMENTOFOTON',
                'nombre' => 'Segmento FOTON',
                'descripcion' => 'Archivo utilizado en la gráfica Ventas por Mercado de la página FOTON.',
            ],
            [
                'codigo' => 'VENTAS_LIGEROS',
                'nombre' => 'Ventas ligeros',
                'descripcion' => 'Archivo utilizado en las páginas FOTON, Clases y No INEGI.',
            ],
            [
                'codigo' => 'VENTASVEHICULOSECOLOGICOS',
                'nombre' => 'Ventas de vehículos ecológicos',
                'descripcion' => 'Archivo utilizado en la página Ecológicos.',
            ],
        ];

        DB::transaction(function () use ($registros) {
            foreach ($registros as $registro) {
                DB::table('excel_sources')->updateOrInsert(
                    [
                        'codigo' => $registro['codigo'],
                    ],
                    [
                        'nombre'      => $registro['nombre'],
                        'descripcion' => $registro['descripcion'],
                        'estado'      => 2,
                        'updated_at'  => now(),
                    ]
                );
            }
        });
    }
}
