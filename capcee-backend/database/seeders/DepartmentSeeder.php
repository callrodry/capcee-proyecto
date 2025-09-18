<?php
// database/seeders/DepartmentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            [
                'code' => 'CAPCEE',
                'nombre' => 'CAPCEE Principal',
                'descripcion' => 'Departamento principal de Control y Administración Presupuestal',
                'limite_archivo_diario' => 100,
                'tamano_archivo_maximo_mb' => 50,
                'esta_activo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'OBRAS',
                'nombre' => 'Departamento de Obras',
                'descripcion' => 'Gestión de obras y construcciones',
                'limite_archivo_diario' => 100,
                'tamano_archivo_maximo_mb' => 50,
                'esta_activo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'PAGOS',
                'nombre' => 'Departamento de Pagos',
                'descripcion' => 'Control de pagos y transferencias',
                'limite_archivo_diario' => 100,
                'tamano_archivo_maximo_mb' => 50,
                'esta_activo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'BANORTE',
                'nombre' => 'Banorte',
                'descripcion' => 'Pagos y transferencias Banorte',
                'limite_archivo_diario' => 100,
                'tamano_archivo_maximo_mb' => 50,
                'esta_activo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'GENERAL',
                'nombre' => 'General',
                'descripcion' => 'Departamento general para archivos sin clasificar',
                'limite_archivo_diario' => 100,
                'tamano_archivo_maximo_mb' => 50,
                'esta_activo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->insertOrIgnore($dept);
        }

        $this->command->info('✅ Departamentos creados exitosamente!');
    }
}