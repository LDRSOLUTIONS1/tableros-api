<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'name' => 'Super Administrador',
            'description' => 'Acceso completo'
        ]);

        Role::create([
            'name' => 'Administrador',
            'description' => 'Acceso limitado'
        ]);

        Role::create([
            'name' => 'Limitado',
            'description' => 'Acceso a solo sus tableros asignados'
        ]);

        Role::create([
            'name' => 'Consultor',
            'description' => 'Acceso visualización'
        ]);
    }
}
