<?php

namespace App\Jobs;

use App\Models\ArchivosProcesados;
use App\Services\ExcelProcessor;
use App\Events\ArchivoProcesado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcesarArchivoExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $archivoId;
    public $tries = 3;
    public $timeout = 3600; // 1 hora para archivos grandes

    public function __construct($archivoId)
    {
        $this->archivoId = $archivoId;
    }

    public function handle()
    {
        $archivo = ArchivosProcesados::find($this->archivoId);
        
        if (!$archivo) {
            Log::error("Archivo no encontrado: {$this->archivoId}");
            return;
        }

        Log::info("Iniciando procesamiento de archivo: {$archivo->nombre_archivo}");

        $processor = new ExcelProcessor($archivo);
        $resultado = $processor->procesar();

        // Emitir evento para notificaciones en tiempo real
        event(new ArchivoProcesado($archivo, $resultado));

        Log::info("Procesamiento completado para archivo: {$archivo->nombre_archivo}", [
            'exitoso' => $resultado,
            'registros_procesados' => $archivo->registros_totales,
            'registros_exitosos' => $archivo->registros_exitosos
        ]);
    }

    public function failed(\Throwable $exception)
    {
        $archivo = ArchivosProcesados::find($this->archivoId);
        
        if ($archivo) {
            $archivo->cambiarEstado(ArchivosProcesados::ESTADO_ERROR);
            $archivo->errores = json_encode(['Error fatal: ' . $exception->getMessage()]);
            $archivo->save();
        }

        Log::error("Job fallido para archivo: {$this->archivoId}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}