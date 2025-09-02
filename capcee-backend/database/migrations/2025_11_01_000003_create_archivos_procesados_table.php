<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('archivos_procesados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_archivo');
            $table->string('tipo_archivo', 10); // xlsx, xls
            $table->unsignedBigInteger('departamento_id');
            $table->unsignedBigInteger('usuario_id');
            $table->enum('estado', ['pendiente', 'en_proceso', 'convertido', 'validado', 'error'])
                  ->default('pendiente');
            $table->datetime('fecha_upload');
            $table->datetime('fecha_inicio_procesamiento')->nullable();
            $table->datetime('fecha_fin_procesamiento')->nullable();
            $table->integer('registros_totales')->default(0);
            $table->integer('registros_exitosos')->default(0);
            $table->integer('registros_fallidos')->default(0);
            $table->integer('registros_duplicados')->default(0);
            $table->text('errores')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('ruta_archivo_original');
            $table->integer('tamano_archivo_kb');
            $table->integer('tiempo_procesamiento_segundos')->nullable();
            $table->string('hash_archivo', 64)->nullable(); // Para detectar duplicados
            $table->json('metadatos')->nullable(); // Info adicional del archivo
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('departamento_id')->references('id')->on('departments');
            $table->foreign('usuario_id')->references('id')->on('users');
            
            // Índices
            $table->index('estado');
            $table->index('fecha_upload');
            $table->index('departamento_id');
            $table->index('usuario_id');
            $table->index('hash_archivo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('archivos_procesados');
    }
};