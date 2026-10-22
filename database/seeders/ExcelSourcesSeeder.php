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
                'codigo'      => 'PRODUCCION',
                'nombre'      => 'PRODUCCION',
                'descripcion' => 'PRODUCCION',
                'estado'      => 2,
            ],
            [
                'codigo'      => 'EXPORTACION',
                'nombre'      => 'EXPORTACION',
                'descripcion' => 'EXPORTACION',
                'estado'      => 2,
            ],
            [
                'codigo'      => 'VENTASLIGEROS',
                'nombre'      => 'VENTASLIGEROS',
                'descripcion' => 'VENTASLIGEROS',
                'estado'      => 2,
            ]
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
