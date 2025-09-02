<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AsignacionesCampos;

class AsignacionesCamposSeeder extends Seeder
{
    public function run()
    {
        // Mapeo para SEGUIMIENTO_PAGOS
        $mapeoSeguimiento = [
            ['excel_column' => 'NÚMERO', 'campo_de_base_de_datos' => 'folio1', 'data_type' => 'number', 'is_required' => true],
            ['excel_column' => 'ESTADO', 'campo_de_base_de_datos' => 'status', 'data_type' => 'string', 'is_required' => true],
            ['excel_column' => 'FECHA (ORLI)', 'campo_de_base_de_datos' => 'fecha', 'data_type' => 'date', 'is_required' => true],
            ['excel_column' => 'FOLIO (ORLI)', 'campo_de_base_de_datos' => 'folio2', 'data_type' => 'number', 'is_required' => false],
            ['excel_column' => 'PROVEEDOR (ORLI)', 'campo_de_base_de_datos' => 'empresa', 'data_type' => 'string', 'is_required' => false],
            ['excel_column' => 'IMPORTE (ORLI)', 'campo_de_base_de_datos' => 'total_pagado_por_obra', 'data_type' => 'number', 'is_required' => true],
            ['excel_column' => 'DESCRIPCION GASTO FACTURA (ORLI)', 'campo_de_base_de_datos' => 'observaciones', 'data_type' => 'string', 'is_required' => false],
            ['excel_column' => 'OBRA', 'campo_de_base_de_datos' => 'obra', 'data_type' => 'string', 'is_required' => true],
        ];

        foreach ($mapeoSeguimiento as $mapeo) {
            AsignacionesCampos::updateOrCreate(
                [
                    'codigo_departamento' => 'CAPCEE',
                    'file_type' => 'SEGUIMIENTO_PAGOS',
                    'excel_column' => $mapeo['excel_column']
                ],
                array_merge($mapeo, [
                    'validacion_de_reglas' => json_encode([]),
                    'transformacion_de_reglas' => json_encode(['trim' => true, 'uppercase' => true])
                ])
            );
        }

        // Mapeo para OBRAS_2025
        $mapeoObras = [
            ['excel_column' => 'FOLIO1', 'campo_de_base_de_datos' => 'folio1', 'data_type' => 'number', 'is_required' => true],
            ['excel_column' => 'PARTIDA', 'campo_de_base_de_datos' => 'partida', 'data_type' => 'string', 'is_required' => true],
            ['excel_column' => 'CCT', 'campo_de_base_de_datos' => 'cct', 'data_type' => 'string', 'is_required' => false],
            ['excel_column' => 'PROGRAMA', 'campo_de_base_de_datos' => 'programa', 'data_type' => 'string', 'is_required' => true],
            ['excel_column' => 'OBRA', 'campo_de_base_de_datos' => 'obra', 'data_type' => 'string', 'is_required' => true],
            ['excel_column' => 'MUNICIPIO', 'campo_de_base_de_datos' => 'municipio', 'data_type' => 'string', 'is_required' => true],
            ['excel_column' => 'LOCALIDAD', 'campo_de_base_de_datos' => 'localidad', 'data_type' => 'string', 'is_required' => false],
            ['excel_column' => 'CONTRATO', 'campo_de_base_de_datos' => 'contrato', 'data_type' => 'string', 'is_required' => true],
            ['excel_column' => 'EMPRESA', 'campo_de_base_de_datos' => 'empresa', 'data_type' => 'string', 'is_required' => true],
            ['excel_column' => 'RFC', 'campo_de_base_de_datos' => 'rfc', 'data_type' => 'string', 'is_required' => true],
        ];

        foreach ($mapeoObras as $mapeo) {
            AsignacionesCampos::updateOrCreate(
                [
                    'codigo_departamento' => 'CAPCEE',
                    'file_type' => 'OBRAS_2025',
                    'excel_column' => $mapeo['excel_column']
                ],
                array_merge($mapeo, [
                    'validacion_de_reglas' => json_encode([]),
                    'transformacion_de_reglas' => json_encode(['trim' => true])
                ])
            );
        }
    }
}