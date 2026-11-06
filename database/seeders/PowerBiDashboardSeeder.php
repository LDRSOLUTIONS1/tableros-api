<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PowerBiDashboard;

class PowerBiDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $dashboards = [

            /*
            |--------------------------------------------------------------------------
            | Tablero existente
            |--------------------------------------------------------------------------
            */
            [
                'category_id' => 1,
                'nombre' => 'AVANCE LIGEROS SEPTIEMBRE 2026',
                'descripcion' => 'VENTAS LIGEROS POR MARCA SEPTIEMBRE 2026',
                'url' => 'https://app.powerbi.com/view?r=eyJrIjoiOGQ4OWY2MDQtOGZhMS00ZGJkLTgyYzItN2RhMjEzZTY2YjM5IiwidCI6ImJhZDcyM2NlLTYyYjMtNDExMC04ODdhLTQ1YTE5ZmRjNTkwYiJ9',
                'fuente' => 'IM- INEGI',
            ],

            /*
            |--------------------------------------------------------------------------
            | TABLERO 1: VEHÍCULOS COMERCIALES LIGEROS
            |--------------------------------------------------------------------------
            */
            [
                'category_id' => 1,
                'nombre' => 'TABLERO DE VENTAS DE VEHÍCULOS COMERCIALES LIGEROS',
                'descripcion' => 'Tablero de ventas de vehículos comerciales ligeros. Incluye producción, exportación, ventas y análisis de competencia.',
                'url' => 'https://app.powerbi.com/view?r=eyJrIjoiNjI4YzZhMTgtMDcxMS00ZDgwLTlkODktN2UwYTJiYzYxNWM5IiwidCI6ImJhZDcyM2NlLTYyYjMtNDExMC04ODdhLTQ1YTE5ZmRjNTkwYiJ9',
                'fuente' => 'Inteligencia de Mercado',
            ],

            /*
            |--------------------------------------------------------------------------
            | TABLERO 2: VEHÍCULOS HÍBRIDOS Y ELÉCTRICOS
            |--------------------------------------------------------------------------
            */
            [
                'category_id' => 1,
                'nombre' => 'TABLERO DE VENTAS DE VEHÍCULOS HÍBRIDOS Y ELÉCTRICOS',
                'descripcion' => 'Tablero de ventas de vehículos híbridos y eléctricos. Incluye ventas generales, histórico, regiones y modelos.',
                'url' => 'https://app.powerbi.com/view?r=eyJrIjoiODMwNTQwZWQtMWU4OS00YTMzLWE4OWQtMjdlYjA2ZjYwYTc4IiwidCI6ImJhZDcyM2NlLTYyYjMtNDExMC04ODdhLTQ1YTE5ZmRjNTkwYiJ9',
                'fuente' => 'Inteligencia de Mercado',
            ],

            /*
            |--------------------------------------------------------------------------
            | TABLERO 3: VEHÍCULOS LIGEROS Y COMPARATIVOS
            |--------------------------------------------------------------------------
            */
            [
                'category_id' => 1,
                'nombre' => 'TABLERO DE VEHÍCULOS LIGEROS Y COMPARATIVOS',
                'descripcion' => 'Tablero de vehículos ligeros y comparativos. Incluye ventas FOTON, clases, vehículos ecológicos y comparativos técnicos y comerciales.',
                'url' => 'https://app.powerbi.com/view?r=eyJrIjoiM2NjYmIwMjAtMmI1YS00ODMwLTgxZDYtY2ExZTIxNThiNjkxIiwidCI6ImJhZDcyM2NlLTYyYjMtNDExMC04ODdhLTQ1YTE5ZmRjNTkwYiJ9&pageName=009f8ee7d3aada75cb64',
                'fuente' => 'Inteligencia de Mercado',
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
