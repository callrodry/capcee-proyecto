<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            [
                'code' => 'CAPCEE',
                'nombre' => 'Comité Administrador Poblano para la Construcción de Espacios Educativos',
                'descripcion' => 'Departamento principal de control de obras educativas',
                'limite_archivo_diario' => 100,
                'tamano_archivo_maximo_mb' => 50
            ],
            [
                'code' => 'CONTAB',
                'nombre' => 'Contabilidad y Control Presupuestal',
                'descripcion' => 'Área de control financiero y presupuestal',
                'limite_archivo_diario' => 50,
                'tamano_archivo_maximo_mb' => 30
            ],
            [
                'code' => 'OBRAS',
                'nombre' => 'Dirección de Obras',
                'descripcion' => 'Supervisión y control de obras',
                'limite_archivo_diario' => 75,
                'tamano_archivo_maximo_mb' => 40
            ]
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                $dept
            );
        }
    }
}