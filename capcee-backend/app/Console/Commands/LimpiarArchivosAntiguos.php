<?php

namespace App\Console\Commands;

use App\Models\ArchivosProcesados;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class LimpiarArchivosAntiguos extends Command
{
    protected $signature = 'archivos:limpiar 
                            {--dias=30 : Archivos más antiguos a estos días serán eliminados}
                            {--dry-run : Solo mostrar qué archivos serían eliminados}';
    
    protected $description = 'Limpiar archivos procesados antiguos';

    public function handle()
    {
        $dias = $this->option('dias');
        $dryRun = $this->option('dry-run');
        
        $fecha = now()->subDays($dias);
        
        $archivos = ArchivosProcesados::where('fecha_upload', '<', $fecha)
            ->whereIn('estado', ['convertido', 'validado'])
            ->get();
        
        if ($archivos->isEmpty()) {
            $this->info('No hay archivos para limpiar.');
            return Command::SUCCESS;
        }
        
        $this->info("Se encontraron {$archivos->count()} archivo(s) para limpiar.");
        
        if ($dryRun) {
            $this->table(
                ['ID', 'Nombre', 'Fecha Upload', 'Tamaño (KB)'],
                $archivos->map(function ($archivo) {
                    return [
                        $archivo->id,
                        $archivo->nombre_archivo,
                        $archivo->fecha_upload->format('Y-m-d'),
                        number_format($archivo->tamano_archivo_kb, 2)
                    ];
                })
            );
            
            $this->info('Modo dry-run: No se eliminó ningún archivo.');
            return Command::SUCCESS;
        }
        
        if (!$this->confirm('¿Desea continuar con la eliminación?')) {
            return Command::SUCCESS;
        }
        
        $bar = $this->output->createProgressBar($archivos->count());
        $bar->start();
        
        foreach ($archivos as $archivo) {
            // Eliminar archivo físico
            if (Storage::exists($archivo->ruta_archivo_original)) {
                Storage::delete($archivo->ruta_archivo_original);
            }
            
            // Eliminar registros relacionados
            $archivo->registrosFinancieros()->delete();
            $archivo->logs()->delete();
            $archivo->delete();
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Archivos eliminados exitosamente.');
        
        return Command::SUCCESS;
    }
}