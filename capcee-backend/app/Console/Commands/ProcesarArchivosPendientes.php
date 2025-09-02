<?php

namespace App\Console\Commands;

use App\Models\ArchivosProcesados;
use App\Jobs\ProcesarArchivoExcel;
use Illuminate\Console\Command;

class ProcesarArchivosPendientes extends Command
{
    protected $signature = 'archivos:procesar-pendientes 
                            {--limit=10 : Número máximo de archivos a procesar}
                            {--departamento= : ID del departamento específico}';
    
    protected $description = 'Procesar archivos Excel pendientes';

    public function handle()
    {
        $limit = $this->option('limit');
        $departamentoId = $this->option('departamento');
        
        $query = ArchivosProcesados::pendientes();
        
        if ($departamentoId) {
            $query->where('departamento_id', $departamentoId);
        }
        
        $archivos = $query->limit($limit)->get();
        
        if ($archivos->isEmpty()) {
            $this->info('No hay archivos pendientes para procesar.');
            return Command::SUCCESS;
        }
        
        $this->info("Procesando {$archivos->count()} archivo(s)...");
        
        $bar = $this->output->createProgressBar($archivos->count());
        $bar->start();
        
        foreach ($archivos as $archivo) {
            ProcesarArchivoExcel::dispatch($archivo->id);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Archivos despachados para procesamiento.');
        
        return Command::SUCCESS;
    }
}