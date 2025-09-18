<?php
// app/Http/Controllers/ExcelUploadController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Jobs\ProcessExcelStaging;

class ExcelUploadController extends Controller
{
    public function upload(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:51200'
            ]);

            $file = $request->file('file');
            $departamento = $this->detectarDepartamento($file->getClientOriginalName());
            $path = $file->store('excel_uploads/' . date('Y/m'));
            
            $uploadId = DB::table('file_uploads')->insertGetId([
                'uuid' => Str::uuid(),
                'nombre_archivo_original' => $file->getClientOriginalName(),
                'ruta_archivo' => $path,
                'tamano_archivo' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'departamento' => $departamento,
                'status' => 'PENDIENTE',
                'usuario_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $this->cargarAStaging($uploadId, $path);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Archivo cargado exitosamente',
                'upload_id' => $uploadId,
                'departamento' => $departamento
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en upload: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar archivo: ' . $e->getMessage()
            ], 422);
        }
    }

    private function detectarDepartamento($filename)
    {
        $filename = strtoupper($filename);
        
        if (strpos($filename, 'BANORTE') !== false) return 'BANORTE';
        if (strpos($filename, 'OBRAS') !== false) return 'OBRAS';
        if (strpos($filename, 'PAGOS') !== false) return 'PAGOS';
        if (strpos($filename, 'SEGUIMIENTO') !== false) return 'CAPCEE';
        
        return 'GENERAL';
    }

    private function cargarAStaging($uploadId, $path)
    {
        // Por ahora solo guardamos el registro
        // La lógica de procesamiento Excel la agregaremos después
        DB::table('file_uploads')
            ->where('id', $uploadId)
            ->update([
                'status' => 'COMPLETADO',
                'updated_at' => now()
            ]);
    }

    public function getStatus($uploadId)
    {
        $upload = DB::table('file_uploads')->where('id', $uploadId)->first();
        
        if (!$upload) {
            return response()->json(['error' => 'Upload no encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'upload' => $upload
        ]);
    }
}