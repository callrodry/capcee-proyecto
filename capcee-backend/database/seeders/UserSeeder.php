<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Asegurar que existe al menos un departamento
        $department = Department::firstOrCreate(
            ['code' => 'CAPCEE'],
            [
                'nombre' => 'CAPCEE - Principal',
                'descripcion' => 'Departamento principal',
                'limite_archivo_diario' => 100,
                'tamano_archivo_maximo_mb' => 50,
                'esta_activo' => true
            ]
        );

        // Crear usuarios con departamento
        User::updateOrCreate(
            ['email' => 'admin@capcee.com'],
            [
                'name' => 'Administrador',
                'password' => 'admin123',
                'departamento_id' => $department->id,
                'email_verified_at' => now()
            ]
        );

        User::updateOrCreate(
            ['email' => 'demo@capcee.com'],
            [
                'name' => 'Usuario Demo',
                'password' => 'demo123',
                'departamento_id' => $department->id,
                'email_verified_at' => now()
            ]
        );

        $this->command->info('✅ Usuarios creados exitosamente!');
        $this->command->info('📧 Email: admin@capcee.com');
        $this->command->info('🔑 Password: admin123');
    }
}