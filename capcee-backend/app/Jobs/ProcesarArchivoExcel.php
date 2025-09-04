// app/Jobs/ProcessExcelFile.php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BaseFinancieraImport;
use Illuminate\Support\Facades\DB;

class ProcessExcelFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uploadId;

    public function __construct($uploadId)
    {
        $this->uploadId = $uploadId;
    }

    public function handle()
    {
        $upload = DB::table('file_uploads')->where('id', $this->uploadId)->first();
        
        if (!$upload) {
            return;
        }

        try {
            DB::table('file_uploads')
                ->where('id', $this->uploadId)
                ->update(['status' => 'EN_PROCESO']);

            Excel::import(
                new BaseFinancieraImport,
                storage_path('app/' . $upload->ruta_archivo)
            );

            DB::table('file_uploads')
                ->where('id', $this->uploadId)
                ->update([
                    'status' => 'CONVERTIDO',
                    'procesado_en' => now()
                ]);

        } catch (\Exception $e) {
            DB::table('file_uploads')
                ->where('id', $this->uploadId)
                ->update([
                    'status' => 'ERROR',
                    'error_mensaje' => $e->getMessage()
                ]);
        }
    }
}