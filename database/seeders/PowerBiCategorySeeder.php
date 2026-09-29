<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PowerBiCategory;

class PowerBiCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nombre' => 'Vehículos Ligeros',
                'descripcion' => 'Tableros relacionados con vehículos ligeros.',
                'estado' => 1,
            ],
            [
                'nombre' => 'Vehículos Pesados',
                'descripcion' => 'Tableros relacionados con vehículos pesados.',
                'estado' => 1,
            ],
            [
                'nombre' => 'Emplacamientos Tractos y Remolques',
                'descripcion' => 'Tableros relacionados con emplacamientos de tractos y remolques.',
                'estado' => 1,
            ],
            [
                'nombre' => 'Especificaciones Técnicas P&V',
                'descripcion' => 'Tableros relacionados con especificaciones técnicas de P&V.',
                'estado' => 1,
            ],
        ];

        foreach ($categories as $category) {
            PowerBiCategory::updateOrCreate(
                ['nombre' => $category['nombre']],
                $category
            );
        }
    }
}
