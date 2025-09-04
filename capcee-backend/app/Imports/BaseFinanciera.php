// app/Imports/BaseFinancieraImport.php
<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BaseFinancieraImport implements ToCollection, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    public function collection(Collection $rows)
    {
        $data = [];
        
        foreach ($rows as $row) {
            // Limpiar y preparar datos
            $data[] = [
                'folio1' => $this->cleanNumeric($row['folio1'] ?? null),
                'partida' => $this->cleanText($row['partida'] ?? null),
                'cct' => $this->cleanText($row['cct'] ?? null),
                'folio2' => $this->cleanNumeric($row['folio2'] ?? null),
                'programa' => $this->cleanText($row['programa'] ?? null),
                'municipio' => $this->cleanText($row['municipio'] ?? null),
                'localidad' => $this->cleanText($row['localidad'] ?? null),
                'importe_autorizado' => $this->cleanDecimal($row['importe_autorizado'] ?? null),
                'fecha_de_autorizacion' => $this->cleanDate($row['fecha_de_autorizacion'] ?? null),
                'fuente_datos' => 'EXCEL_SEGUIMIENTO',
                'validado' => false,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Insertar en lotes
        if (!empty($data)) {
            DB::table('base_financiera')->insert($data);
        }
    }

    public function chunkSize(): int
    {
        return 1000; // Procesar en chunks de 1000 registros
    }

    public function batchSize(): int
    {
        return 500; // Insertar en lotes de 500
    }

    private function cleanText($value)
    {
        return $value ? trim($value) : null;
    }

    private function cleanNumeric($value)
    {
        if (!$value) return null;
        
        // Remover caracteres no numéricos
        $cleaned = preg_replace('/[^0-9.]/', '', $value);
        return is_numeric($cleaned) ? $cleaned : null;
    }

    private function cleanDecimal($value)
    {
        if (!$value) return null;
        
        // Remover símbolos de moneda y espacios
        $cleaned = str_replace(['$', ',', ' '], '', $value);
        return is_numeric($cleaned) ? $cleaned : null;
    }

    private function cleanDate($value)
    {
        if (!$value) return null;
        
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}