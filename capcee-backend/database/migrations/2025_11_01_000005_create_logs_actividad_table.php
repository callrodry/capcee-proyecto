<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('logs_actividad', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->unsignedBigInteger('archivo_procesado_id')->nullable();
            $table->string('accion', 100); // upload, proceso_iniciado, proceso_completado, error
            $table->text('descripcion');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('datos_adicionales')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('usuario_id')->references('id')->on('users');
            $table->foreign('archivo_procesado_id')->references('id')->on('archivos_procesados');
            
            // Índices
            $table->index('usuario_id');
            $table->index('archivo_procesado_id');
            $table->index('accion');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('logs_actividad');
    }
};