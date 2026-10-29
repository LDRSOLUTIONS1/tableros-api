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
                'nombre' => 'AVANCE LIGEROS SEPTIEMBRE 2026',
                'descripcion' => 'VENTAS LIGEROS POR MARCA SEPTIEMBRE 2026',
                'url' => 'https://app.powerbi.com/view?r=eyJrIjoiOGQ4OWY2MDQtOGZhMS00ZGJkLTgyYzItN2RhMjEzZTY2YjM5IiwidCI6ImJhZDcyM2NlLTYyYjMtNDExMC04ODdhLTQ1YTE5ZmRjNTkwYiJ9',
                'fuente' => 'IM- INEGI',
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
