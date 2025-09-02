<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArchivosProcesados;
use App\Models\Department;
use App\Jobs\ProcesarArchivoExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ArchivoController extends Controller
{
    /**
     * Listar archivos con paginación
     */
    public function index(Request $request)
    {
        $query = ArchivosProcesados::with(['departamento', 'usuario']);

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_upload', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_upload', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $query->orderBy('created_at', 'desc');

        return response()->json([
            'success' => true,
            'data' => $query->paginate($request->per_page ?? 15)
        ]);
    }

    /**
     * Upload masivo de archivos
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'archivos' => 'required|array|max:20',
            'archivos.*' => 'required|file|mimes:xlsx,xls|max:51200', // 50MB
            'departamento_id' => 'required|exists:departments,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $departamento = Department::find($request->departamento_id);
        
        // Verificar límite diario
        if (!$departamento->puedeSubirArchivos()) {
            return response()->json([
                'success' => false,
                'message' => "Límite diario alcanzado. Archivos restantes: {$departamento->archivosRestantesHoy()}"
            ], 429);
        }

        $archivosSubidos = [];
        $errores = [];

        foreach ($request->file('archivos') as $archivo) {
            try {
                // Generar hash para detectar duplicados
                $hash = hash_file('sha256', $archivo->getRealPath());
                
                // Verificar si ya existe
                if (ArchivosProcesados::where('hash_archivo', $hash)->exists()) {
                    $errores[] = "Archivo duplicado: {$archivo->getClientOriginalName()}";
                    continue;
                }

                // Guardar archivo
                $path = $archivo->store('uploads/' . date('Y/m/d'));
                
                // Crear registro en BD
                $archivoProcesado = ArchivosProcesados::create([
                    'nombre_archivo' => $archivo->getClientOriginalName(),
                    'tipo_archivo' => $archivo->getClientOriginalExtension(),
                    'departamento_id' => $request->departamento_id,
                    'usuario_id' => auth()->id(),
                    'fecha_upload' => now(),
                    'ruta_archivo_original' => $path,
                    'tamano_archivo_kb' => $archivo->getSize() / 1024,
                    'hash_archivo' => $hash,
                    'metadatos' => [
                        'mime_type' => $archivo->getMimeType(),
                        'original_name' => $archivo->getClientOriginalName()
                    ]
                ]);

                // Despachar job para procesamiento asíncrono
                ProcesarArchivoExcel::dispatch($archivoProcesado->id);
                
                $archivosSubidos[] = $archivoProcesado;

            } catch (\Exception $e) {
                $errores[] = "Error subiendo {$archivo->getClientOriginalName()}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'archivos_subidos' => count($archivosSubidos),
            'archivos' => $archivosSubidos,
            'errores' => $errores
        ], 201);
    }

    /**
     * Ver detalle de archivo
     */
    public function show($id)
    {
        $archivo = ArchivosProcesados::with(['departamento', 'usuario', 'logs'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $archivo
        ]);
    }

    /**
     * Obtener estado de procesamiento
     */
    public function estado($id)
    {
        $archivo = ArchivosProcesados::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'estado' => $archivo->estado,
                'progreso' => [
                    'total' => $archivo->registros_totales,
                    'exitosos' => $archivo->registros_exitosos,
                    'fallidos' => $archivo->registros_fallidos,
                    'duplicados' => $archivo->registros_duplicados,
                    'porcentaje_exito' => $archivo->getPorcentajeExito()
                ],
                'tiempo_procesamiento' => $archivo->getTiempoProcesamiento(),
                'errores' => json_decode($archivo->errores ?? '[]')
            ]
        ]);
    }

    /**
     * Reintentar procesamiento
     */
    public function reintento($id)
    {
        $archivo = ArchivosProcesados::findOrFail($id);

        if (!in_array($archivo->estado, ['error', 'validado'])) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo no puede ser reprocesado en su estado actual'
            ], 422);
        }

        // Resetear estadísticas
        $archivo->update([
            'estado' => ArchivosProcesados::ESTADO_PENDIENTE,
            'registros_totales' => 0,
            'registros_exitosos' => 0,
            'registros_fallidos' => 0,
            'registros_duplicados' => 0,
            'errores' => null,
            'fecha_inicio_procesamiento' => null,
            'fecha_fin_procesamiento' => null,
            'tiempo_procesamiento_segundos' => null
        ]);

        // Despachar nuevo job
        ProcesarArchivoExcel::dispatch($archivo->id);

        return response()->json([
            'success' => true,
            'message' => 'Reprocesamiento iniciado'
        ]);
    }

    /**
     * Vista previa de datos
     */
    public function preview($id)
    {
        $archivo = ArchivosProcesados::findOrFail($id);
        
        $registros = $archivo->registrosFinancieros()
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_registros' => $archivo->registrosFinancieros()->count(),
                'muestra' => $registros
            ]
        ]);
    }

    /**
     * Eliminar archivo
     */
    public function destroy($id)
    {
        $archivo = ArchivosProcesados::findOrFail($id);

        // Eliminar archivo físico
        Storage::delete($archivo->ruta_archivo_original);

        // Eliminar registros relacionados
        $archivo->registrosFinancieros()->delete();
        $archivo->logs()->delete();

        // Eliminar registro principal
        $archivo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Archivo eliminado correctamente'
        ]);
    }
}