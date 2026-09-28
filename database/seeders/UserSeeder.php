<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'role_id' => 1,
            'collaborator_number' => '12345',
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => Hash::make('password')
        ]);
    }
}
