<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PowerBiDashboard;

class PowerBiDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $dashboards = [
            [
                'category_id' => 1,
                'nombre' => 'Avance Ventas Mensuales Vehículos Ligeros (Marca)',
                'fuente' => 'INEGI',
                'url' => 'https://URL_DEL_TABLERO_1',
            ],
            [
                'category_id' => 1,
                'nombre' => 'Reporte Industria Automotriz Ventas Mensuales',
                'fuente' => 'INEGI',
                'url' => 'https://URL_DEL_TABLERO_2',
            ],
            [
                'category_id' => 1,
                'nombre' => 'Reporte Mensual Ventas Ligeros Segmentos FOTON e Industria',
                'fuente' => 'INEGI',
                'url' => 'https://URL_DEL_TABLERO_3',
            ],
            [
                'category_id' => 1,
                'nombre' => 'Reporte Mensual Ventas Ligeros Híbridos y Eléctricos Industria',
                'fuente' => 'INEGI',
                'url' => 'https://URL_DEL_TABLERO_4',
            ],

            [
                'category_id' => 2,
                'nombre' => 'Reporte Industria Automotriz Ventas Mensuales Vehículos Pesados',
                'fuente' => 'INEGI',
                'url' => 'https://URL_DEL_TABLERO_5',
            ],
            [
                'category_id' => 2,
                'nombre' => 'Informe Mensual Ventas Vehículos Pesados Mayoreo FOTON e Industria',
                'fuente' => 'INEGI',
                'url' => 'https://URL_DEL_TABLERO_6',
            ],
            [
                'category_id' => 2,
                'nombre' => 'Informe Mensual Ventas Vehículos Pesados Menudeo FOTON e Industria',
                'fuente' => 'INEGI',
                'url' => 'https://URL_DEL_TABLERO_7',
            ],
            [
                'category_id' => 2,
                'nombre' => 'Informe Mensual Ventas Vehículos Pesados Mayoreo FOTON e Industria',
                'fuente' => 'ANPACT',
                'url' => 'https://URL_DEL_TABLERO_8',
            ],
            [
                'category_id' => 2,
                'nombre' => 'Informe Mensual Ventas Vehículos Pesados Menudeo FOTON e Industria',
                'fuente' => 'ANPACT',
                'url' => 'https://URL_DEL_TABLERO_9',
            ],
        ];

        foreach ($dashboards as $dashboard) {
            PowerBiDashboard::updateOrCreate(
                [
                    'category_id' => $dashboard['category_id'],
                    'nombre' => $dashboard['nombre'],
                ],
                $dashboard
            );
        }
    }
}
