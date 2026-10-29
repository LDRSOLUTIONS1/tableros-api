<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExcelSourcesSeeder extends Seeder
{
    public function run(): void
    {
        $registros = [
            [
                'codigo'      => 'EXPORTACION',
                'nombre'      => 'EXPORTACION',
                'descripcion' => 'EXPORTACION',
                'estado'      => 2,
            ],
            [
                'codigo'      => 'PRODUCCION',
                'nombre'      => 'PRODUCCION',
                'descripcion' => 'PRODUCCION',
                'estado'      => 2,
            ],
            [
                'codigo'      => 'VENTASLIGEROS',
                'nombre'      => 'VENTASLIGEROS',
                'descripcion' => 'VENTASLIGEROS',
                'estado'      => 2,
            ],


            [
                'codigo'      => 'ECOLOGICOS',
                'nombre'      => 'ECOLOGICOS',
                'descripcion' => 'ECOLOGICOS',
                'estado'      => 2,
            ],
            [
                'codigo'      => 'MARCASMODELOS',
                'nombre'      => 'MARCASMODELOS',
                'descripcion' => 'MARCASMODELOS',
                'estado'      => 2,
            ],
            [
                'codigo'      => 'VENTASVEHICULOS',
                'nombre'      => 'VENTASVEHICULOS',
                'descripcion' => 'VENTASVEHICULOS',
                'estado'      => 2,
            ],


            [
                'codigo'      => 'COMPARATIVOS',
                'nombre'      => 'COMPARATIVOS',
                'descripcion' => 'COMPARATIVOS',
                'estado'      => 2,
            ],
            [
                'codigo'      => 'IMAGENESMODELOS',
                'nombre'      => 'IMAGENESMODELOS',
                'descripcion' => 'IMAGENESMODELOS',
                'estado'      => 2,
            ],
            [
                'codigo'      => 'PRECIOSOFERTAS',
                'nombre'      => 'PRECIOSOFERTAS',
                'descripcion' => 'PRECIOSOFERTAS',
                'estado'      => 2,

            ],


            [
                'codigo'      => 'SEGMENTOFOTON',
                'nombre'      => 'SEGMENTOFOTON',
                'descripcion' => 'SEGMENTOFOTON',
                'estado'      => 2,
            ],
            [
                'codigo'      => 'VENTAS_LIGEROS',
                'nombre'      => 'VENTAS_LIGEROS',
                'descripcion' => 'VENTAS_LIGEROS',
                'estado'      => 2,
            ],
            [
                'codigo'      => 'VENTASVEHICULOSECOLOGICOS',
                'nombre'      => 'VENTASVEHICULOSECOLOGICOS',
                'descripcion' => 'VENTASVEHICULOSECOLOGICOS',
                'estado'      => 2,
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
                        'estado'      => $registro['estado'],
                        'updated_at'  => now(),
                    ]
                );
            }
        });
    }
}
