// database/migrations/2024_01_02_000001_create_file_uploads_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('nombre_archivo_original', 255);
            $table->string('ruta_archivo', 500);
            $table->bigInteger('tamano_archivo');
            $table->string('mime_type', 100);
            $table->string('departamento', 50)->default('GENERAL');
            $table->integer('total_filas')->default(0);
            $table->integer('filas_procesadas')->default(0);
            $table->integer('filas_con_error')->default(0);
            $table->enum('status', [
                'PENDIENTE',
                'CARGANDO',
                'TRANSFORMANDO',
                'COMPLETADO',
                'COMPLETADO_CON_ERRORES',
                'ERROR'
            ])->default('PENDIENTE');
            $table->text('mensaje_error')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamps();
            
            $table->foreign('usuario_id')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
            $table->index('departamento');
        });
    }

    public function down()
    {
        Schema::dropIfExists('file_uploads');
    }
};