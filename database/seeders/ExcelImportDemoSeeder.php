<?php

namespace Database\Seeders;

use App\Models\ExcelSource;
use App\Models\ExcelImportVersion;
use App\Models\VentaPublico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExcelImportDemoSeeder extends Seeder
{
    public function run(): void
    {
        $registros = [
            [
                'tema' => 'Venta al público de vehículos',
                'anio' => 2026,
                'mes' => 'Enero',
                'mes_numero' => 1,
                'marca' => 'Acura',
                'modelo' => 'Mdx',
                'tipo' => 'Camiones ligeros',
                'segmento_inegi' => "SUV's",
                'origen' => 'Importado',
                'pais_origen' => 'Canadá',
                'cantidad' => 17,
                'mercado_inegi' => "SUV's",
                'marca_mercado_foton' => 'no',
                'unidad_foton_inegi' => null,
                'seg_pv' => null,
                'origen_2' => null,
            ],
            [
                'tema' => 'Venta al público de vehículos',
                'anio' => 2026,
                'mes' => 'Febrero',
                'mes_numero' => 2,
                'marca' => 'MG Motor',
                'modelo' => 'RX5',
                'tipo' => 'Camiones ligeros',
                'segmento_inegi' => "SUV's",
                'origen' => 'Importado',
                'pais_origen' => 'China',
                'cantidad' => 205,
                'mercado_inegi' => "SUV's",
                'marca_mercado_foton' => 'no',
                'unidad_foton_inegi' => null,
                'seg_pv' => null,
                'origen_2' => null,
            ],
            [
                'tema' => 'Venta al público de vehículos',
                'anio' => 2026,
                'mes' => 'Marzo',
                'mes_numero' => 3,
                'marca' => 'Bentley',
                'modelo' => 'Continental',
                'tipo' => 'Automóviles',
                'segmento_inegi' => 'De Lujo',
                'origen' => 'Importado',
                'pais_origen' => 'Reino Unido',
                'cantidad' => 1,
                'mercado_inegi' => 'De Lujo',
                'marca_mercado_foton' => 'no',
                'unidad_foton_inegi' => null,
                'seg_pv' => null,
                'origen_2' => null,
            ],
            [
                'tema' => 'Venta al público de vehículos',
                'anio' => 2026,
                'mes' => 'Abril',
                'mes_numero' => 4,
                'marca' => 'Acura',
                'modelo' => 'Mdx',
                'tipo' => 'Camiones ligeros',
                'segmento_inegi' => "SUV's",
                'origen' => 'Importado',
                'pais_origen' => 'Canadá',
                'cantidad' => 6,
                'mercado_inegi' => "SUV's",
                'marca_mercado_foton' => 'no',
                'unidad_foton_inegi' => null,
                'seg_pv' => null,
                'origen_2' => null,
            ],
            [
                'tema' => 'Venta al público de vehículos',
                'anio' => 2026,
                'mes' => 'Mayo',
                'mes_numero' => 5,
                'marca' => 'Chevrolet',
                'modelo' => 'Tornado Van',
                'tipo' => 'Camiones ligeros',
                'segmento_inegi' => 'Vans',
                'origen' => 'Importado',
                'pais_origen' => 'China',
                'cantidad' => 1265,
                'mercado_inegi' => 'Vans',
                'marca_mercado_foton' => 'no',
                'unidad_foton_inegi' => null,
                'seg_pv' => 'Compacta',
                'origen_2' => null,
            ],
            [
                'tema' => 'Venta al público de vehículos',
                'anio' => 2026,
                'mes' => 'Julio',
                'mes_numero' => 7,
                'marca' => 'Mitsubishi',
                'modelo' => 'Outlander Sport',
                'tipo' => 'Camiones ligeros',
                'segmento_inegi' => "SUV's",
                'origen' => 'Importado',
                'pais_origen' => 'Indonesia',
                'cantidad' => 416,
                'mercado_inegi' => "SUV's",
                'marca_mercado_foton' => 'no',
                'unidad_foton_inegi' => null,
                'seg_pv' => null,
                'origen_2' => null,
            ],
            [
                'tema' => 'Venta al público de vehículos',
                'anio' => 2026,
                'mes' => 'Agosto',
                'mes_numero' => 8,
                'marca' => 'Foton',
                'modelo' => 'HI-VAN',
                'tipo' => 'Camiones ligeros',
                'segmento_inegi' => 'Vans',
                'origen' => 'Nacional',
                'pais_origen' => 'China',
                'cantidad' => 20,
                'mercado_inegi' => 'Vans',
                'marca_mercado_foton' => 'si',
                'unidad_foton_inegi' => 'Hi Van',
                'seg_pv' => 'Mediana',
                'origen_2' => 'Nacional',
            ],
        ];

        DB::transaction(function () use ($registros) {

            /*
             * PASO 1:
             * Registrar el Excel en el catálogo.
             *
             * firstOrCreate evita duplicar el registro
             * cuando ejecutamos el seeder nuevamente.
             */
            $source = ExcelSource::firstOrCreate(
                [
                    'codigo' => 'VENTAS_PUBLICO',
                ],
                [
                    'nombre' => 'Venta al público de vehículos',
                    'descripcion' =>
                        'Datos de ventas de vehículos exportados desde Power BI.',
                    'estado' => 2,
                ]
            );

            /*
             * PASO 2:
             * Crear la versión inicial del Excel.
             *
             * La ruta es ilustrativa. Este seeder no crea
             * físicamente el archivo XLSX.
             */
            $version = ExcelImportVersion::firstOrCreate(
                [
                    'excel_source_id' => $source->id,
                    'version_number' => 1,
                ],
                [
                    'original_filename' => 'ventas_publico_v1.xlsx',
                    'file_path' =>
                        'demo/ventas-publico/version_1.xlsx',
                    'file_hash' => null,
                    'mime_type' =>
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'file_size' => null,
                    'status' => 'completed',
                    'total_rows' => count($registros),
                    'imported_by' => null,
                    'imported_at' => now(),
                ]
            );

            /*
             * PASO 3:
             * Guardar el snapshot de datos.
             *
             * Solo insertamos los registros cuando se crea
             * por primera vez la versión de demostración.
             */
            if ($version->wasRecentlyCreated) {
                foreach ($registros as $registro) {
                    VentaPublico::create([
                        'excel_import_version_id' => $version->id,
                        ...$registro,
                    ]);
                }
            }

            /*
             * PASO 4:
             * Publicar la versión como vigente.
             *
             * Solo se apunta a versiones completadas.
             */
            if ($version->status === 'completed') {
                $source->update([
                    'current_version_id' => $version->id,
                ]);
            }
        });
    }
}
