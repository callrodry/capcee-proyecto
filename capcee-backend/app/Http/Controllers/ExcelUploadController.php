// app/Http/Controllers/ExcelUploadController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ProcessExcelFile;

class ExcelUploadController extends Controller
{
    private $requiredColumns = [
        'folio1',
        'partida',
        'cct',
        'folio2',
        'programa',
        'municipio',
        'localidad'
    ];

    public function upload(Request $request)
    {
        try {
            // Validación inicial
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:51200', // 50MB
                'departamento_id' => 'required|exists:departamentos,id'
            ]);

            $file = $request->file('file');
            $departamentoId = $request->departamento_id;

            // Guardar archivo temporalmente
            $path = $file->store('temp_excel');
            
            // Validar estructura del Excel
            $validation = $this->validateExcelStructure($path);
            
            if (!$validation['valid']) {
                return response()->json([
                    'error' => 'Estructura de Excel inválida',
                    'details' => $validation['errors']
                ], 422);
            }

            // Crear registro en BD
            $upload = DB::table('file_uploads')->insertGetId([
                'uuid' => \Str::uuid(),
                'nombre_archivo_original' => $file->getClientOriginalName(),
                'ruta_archivo' => $path,
                'tamaño_archivo' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'departamento_id' => $departamentoId,
                'status' => 'PENDIENTE',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Procesar en cola para archivos grandes
            if ($file->getSize() > 5 * 1024 * 1024) { // > 5MB
                ProcessExcelFile::dispatch($upload);
                
                return response()->json([
                    'message' => 'Archivo grande enviado a procesamiento',
                    'upload_id' => $upload,
                    'status' => 'EN_PROCESO'
                ]);
            }

            // Procesar inmediatamente archivos pequeños
            $this->processFile($upload);
            
            return response()->json([
                'message' => 'Archivo procesado exitosamente',
                'upload_id' => $upload,
                'status' => 'CONVERTIDO'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en upload: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al procesar archivo',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function validateExcelStructure($path)
    {
        try {
            $data = Excel::toArray(null, storage_path('app/' . $path));
            
            if (empty($data) || empty($data[0])) {
                return [
                    'valid' => false,
                    'errors' => ['El archivo está vacío']
                ];
            }

            $headers = array_map('strtolower', array_keys($data[0][0]));
            $errors = [];

            // Validar columnas requeridas
            foreach ($this->requiredColumns as $column) {
                if (!in_array(strtolower($column), $headers)) {
                    $errors[] = "Columna requerida faltante: {$column}";
                }
            }

            return [
                'valid' => empty($errors),
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'errors' => ['Error al leer estructura: ' . $e->getMessage()]
            ];
        }
    }

    private function processFile($uploadId)
    {
        $upload = DB::table('file_uploads')->where('id', $uploadId)->first();
        
        try {
            // Actualizar estado
            DB::table('file_uploads')
                ->where('id', $uploadId)
                ->update(['status' => 'EN_PROCESO']);

            // Importar datos
            Excel::import(new \App\Imports\BaseFinancieraImport, 
                         storage_path('app/' . $upload->ruta_archivo));

            // Actualizar estado final
            DB::table('file_uploads')
                ->where('id', $uploadId)
                ->update([
                    'status' => 'CONVERTIDO',
                    'procesado_en' => now()
                ]);

        } catch (\Exception $e) {
            DB::table('file_uploads')
                ->where('id', $uploadId)
                ->update([
                    'status' => 'ERROR',
                    'error_mensaje' => $e->getMessage()
                ]);
            
            throw $e;
        }
    }
}