<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Obtener el departamento CAPCEE
        $department = DB::table('departments')->where('code', 'CAPCEE')->first();
        
        // Crear usuario admin
        User::updateOrCreate(
            ['email' => 'admin@capcee.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'departamento_id' => $department ? $department->id : null,
                'email_verified_at' => now()
            ]
        );

        // Crear usuario demo
        User::updateOrCreate(
            ['email' => 'demo@capcee.com'],
            [
                'name' => 'Usuario Demo',
                'password' => Hash::make('demo123'),
                'departamento_id' => $department ? $department->id : null,
                'email_verified_at' => now()
            ]
        );

        $this->command->info('✅ Usuarios creados exitosamente!');
        $this->command->info('📧 Email: admin@capcee.com');
        $this->command->info('🔑 Password: admin123');
    }
}