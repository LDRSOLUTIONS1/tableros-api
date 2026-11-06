<?php

namespace Database\Seeders;

use App\Models\ExcelSource;
use App\Models\PowerBiDashboard;
use App\Models\PowerBiDashboardPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PowerBiDashboardStructureSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | 1. Obtener fuentes de Excel
            |--------------------------------------------------------------------------
            */

            $sources = ExcelSource::all()->keyBy('codigo');

            /*
            |--------------------------------------------------------------------------
            | 2. Estructura de los tableros
            |--------------------------------------------------------------------------
            */

            $tableros = [

                // TABLERO 1: VEHÍCULOS COMERCIALES LIGEROS
                [
                    'key' => 'ligeros',

                    'aliases' => [
                        'tablero de ventas de vehiculos comerciales ligeros',
                        'ventas de vehiculos comerciales ligeros',
                        'vehiculos comerciales ligeros',
                    ],

                    'paginas' => [

                        [
                            'nombre' => 'Producción y Expo',
                            'descripcion' => 'Página que combina producción y exportación para mostrar ensamble y envíos al extranjero.',
                            'archivos' => [
                                [
                                    'codigo' => 'PRODUCCION',
                                    'instrucciones' => 'Actualiza los indicadores de producción y ensamble de vehículos.',
                                ],
                                [
                                    'codigo' => 'EXPORTACION',
                                    'instrucciones' => 'Actualiza los indicadores de exportación y envío al extranjero.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Ventas en Segmento',
                            'descripcion' => 'Análisis de ventas por segmento.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTASLIGEROS',
                                    'instrucciones' => 'Actualiza la información comercial de ventas por segmento.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Segmentos Competencia',
                            'descripcion' => 'Análisis de segmentos de la competencia.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTASLIGEROS',
                                    'instrucciones' => 'Actualiza la información de segmentos de competencia.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Competencia-FOTON',
                            'descripcion' => 'Comparativo de FOTON frente a sus competidores.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTASLIGEROS',
                                    'instrucciones' => 'Actualiza los indicadores de competencia de FOTON.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Ventas Veh. Ligeros',
                            'descripcion' => 'Información general de ventas de vehículos ligeros.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTASLIGEROS',
                                    'instrucciones' => 'Actualiza los indicadores generales de ventas.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Ventas Pick Ups',
                            'descripcion' => 'Análisis de ventas de Pick Ups.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTASLIGEROS',
                                    'instrucciones' => 'Actualiza las ventas de Pick Ups.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Pick Ups MC',
                            'descripcion' => 'Análisis de Pick Ups en MC.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTASLIGEROS',
                                    'instrucciones' => 'Actualiza los indicadores de Pick Ups MC.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Ventas Vanes',
                            'descripcion' => 'Análisis de ventas de Vanes.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTASLIGEROS',
                                    'instrucciones' => 'Actualiza las ventas de Vanes.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Vanes MC',
                            'descripcion' => 'Análisis de Vanes en MC.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTASLIGEROS',
                                    'instrucciones' => 'Actualiza los indicadores de Vanes MC.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Ventas Mini Trucks',
                            'descripcion' => 'Análisis de ventas de Mini Trucks.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTASLIGEROS',
                                    'instrucciones' => 'Actualiza las ventas de Mini Trucks.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Ventas Clas. 2 & 3',
                            'descripcion' => 'Análisis de ventas de las clasificaciones 2 y 3.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTASLIGEROS',
                                    'instrucciones' => 'Actualiza las ventas de las clases 2 y 3.',
                                ],
                            ],
                        ],
                    ],
                ],

                // TABLERO 2: VEHÍCULOS HÍBRIDOS Y ELÉCTRICOS
                [
                    'key' => 'ecologicos',

                    'aliases' => [
                        'tablero de ventas de vehiculos hibridos y electricos',
                        'ventas de vehiculos hibridos y electricos',
                        'vehiculos hibridos y electricos',
                    ],

                    'paginas' => [

                        [
                            'nombre' => 'Ventas',
                            'descripcion' => 'Resumen comercial general de vehículos híbridos y eléctricos.',
                            'archivos' => [
                                [
                                    'codigo' => 'ECOLOGICOS',
                                    'instrucciones' => 'Actualiza la información comercial de vehículos ecológicos.',
                                ],
                                [
                                    'codigo' => 'VENTASVEHICULOS',
                                    'instrucciones' => 'Complementa la información general de ventas.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Histórico',
                            'descripcion' => 'Tendencias históricas de vehículos ecológicos.',
                            'archivos' => [
                                [
                                    'codigo' => 'ECOLOGICOS',
                                    'instrucciones' => 'Actualiza las tendencias históricas.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Regiones',
                            'descripcion' => 'Segmentación territorial de vehículos ecológicos.',
                            'archivos' => [
                                [
                                    'codigo' => 'ECOLOGICOS',
                                    'instrucciones' => 'Actualiza la información de ventas por región.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'Modelos',
                            'descripcion' => 'Información detallada de marcas y modelos.',
                            'archivos' => [
                                [
                                    'codigo' => 'MARCASMODELOS',
                                    'instrucciones' => 'Actualiza la información de marcas y modelos.',
                                ],
                            ],
                        ],
                    ],
                ],

                // TABLERO 3: VEHÍCULOS LIGEROS Y COMPARATIVOS
                [
                    'key' => 'comparativos',

                    'aliases' => [
                        'tablero de vehiculos ligeros y comparativos',
                        'vehiculos ligeros y comparativos',
                    ],

                    'paginas' => [

                        [
                            'nombre' => 'FOTON',
                            'descripcion' => 'Portada principal del tablero y análisis de ventas por mercado.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTAS_LIGEROS',
                                    'instrucciones' => 'Actualiza la información general de la página FOTON.',
                                ],
                                [
                                    'codigo' => 'SEGMENTOFOTON',
                                    'instrucciones' => 'Actualiza directamente la gráfica Ventas por Mercado.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'CLASES',
                            'descripcion' => 'Clasificación de ventas por clase.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTAS_LIGEROS',
                                    'instrucciones' => 'Actualiza los volúmenes de venta por clase.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'NO INEGI',
                            'descripcion' => 'Registros y clasificaciones no cubiertos por INEGI.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTAS_LIGEROS',
                                    'instrucciones' => 'Actualiza los registros no cubiertos por INEGI.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'ECOLÓGICOS',
                            'descripcion' => 'Seguimiento de unidades ecológicas.',
                            'archivos' => [
                                [
                                    'codigo' => 'VENTASVEHICULOSECOLOGICOS',
                                    'instrucciones' => 'Actualiza las unidades de vehículos ecológicos.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'COMPARATIVOS',
                            'descripcion' => 'Comparativo técnico y comercial de modelos y marcas.',
                            'archivos' => [
                                [
                                    'codigo' => 'COMPARATIVOS',
                                    'instrucciones' => 'Actualiza las especificaciones técnicas y características de los modelos.',
                                ],
                                [
                                    'codigo' => 'IMAGENESMODELOS',
                                    'instrucciones' => 'Actualiza las fotografías de vehículos y logotipos.',
                                ],
                                [
                                    'codigo' => 'PRECIOSOFERTAS',
                                    'instrucciones' => 'Actualiza los precios de lista, descuentos y condiciones comerciales.',
                                ],
                            ],
                        ],

                        [
                            'nombre' => 'COMPARATIVOS ELÉCTRICOS',
                            'descripcion' => 'Comparativo de especificaciones técnicas de vehículos eléctricos.',
                            'archivos' => [
                                [
                                    'codigo' => 'COMPARATIVOS',
                                    'instrucciones' => 'Actualiza las especificaciones técnicas de los modelos eléctricos.',
                                ],
                                [
                                    'codigo' => 'IMAGENESMODELOS',
                                    'instrucciones' => 'Actualiza las fotografías de vehículos y logotipos.',
                                ],
                                [
                                    'codigo' => 'PRECIOSOFERTAS',
                                    'instrucciones' => 'Actualiza los precios y condiciones comerciales.',
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | 3. Registrar páginas y relacionar sus archivos
            |--------------------------------------------------------------------------
            */

            foreach ($tableros as $tableroConfig) {

                $dashboard = $this->buscarDashboard(
                    $tableroConfig['aliases'],
                    $tableroConfig['key']
                );

                foreach ($tableroConfig['paginas'] as $index => $paginaData) {

                    $pagina = PowerBiDashboardPage::updateOrCreate(
                        [
                            'power_bi_dashboard_id' => $dashboard->id,
                            'nombre' => $paginaData['nombre'],
                        ],
                        [
                            'descripcion' => $paginaData['descripcion'],
                            'orden'       => $index + 1,
                            'estado'      => 2,
                        ]
                    );

                    $relaciones = [];

                    foreach ($paginaData['archivos'] as $orden => $archivo) {

                        $source = $sources->get($archivo['codigo']);

                        if (!$source) {
                            throw new RuntimeException(
                                "No existe el Excel con código: {$archivo['codigo']}"
                            );
                        }

                        $relaciones[$source->id] = [
                            'es_requerido'  => true,
                            'instrucciones' => $archivo['instrucciones'],
                            'orden'         => $orden + 1,
                        ];
                    }

                    // Sincroniza los Excel correspondientes a esta página.
                    $pagina->excelSources()->sync($relaciones);
                }
            }
        });
    }

    /**
     * Busca un tablero existente por sus nombres posibles.
     */
    private function buscarDashboard(
        array $aliases,
        string $key
    ): PowerBiDashboard {

        $dashboards = PowerBiDashboard::all();

        foreach ($aliases as $alias) {

            $busqueda = Str::ascii(Str::lower($alias));

            $dashboard = $dashboards->first(function ($item) use ($busqueda) {

                $nombre = Str::ascii(
                    Str::lower($item->nombre)
                );

                return Str::contains($nombre, $busqueda);
            });

            if ($dashboard) {
                return $dashboard;
            }
        }

        throw new RuntimeException(
            "No se encontró el tablero '{$key}'. Tableros disponibles: "
                . $dashboards->pluck('nombre')->implode(', ')
                . ". Revisa los aliases de PowerBiDashboardStructureSeeder."
        );
    }
}
