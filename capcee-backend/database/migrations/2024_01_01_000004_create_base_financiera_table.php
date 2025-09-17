// database/migrations/2024_01_02_000003_create_base_financiera_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('base_financiera', function (Blueprint $table) {
            $table->id();
            
            // Campos principales flexibles
            $table->decimal('folio1', 10, 0)->nullable();
            $table->text('partida')->nullable();
            $table->string('cct', 50)->nullable();
            $table->decimal('folio2', 10, 0)->nullable();
            $table->text('programa')->nullable();
            
            // Información bancaria
            $table->string('cuenta_bancaria', 50)->nullable();
            $table->string('clabe_interbancaria', 50)->nullable();
            
            // Autorización
            $table->string('folio_de_autorizacion', 100)->nullable();
            $table->text('fecha_de_autorizacion')->nullable(); // TEXT para manejar diferentes formatos
            $table->text('importe_autorizado')->nullable(); // TEXT para manejar formatos con símbolos
            
            // Ubicación
            $table->string('municipio', 100)->nullable();
            $table->string('localidad', 100)->nullable();
            
            // Obra
            $table->text('nombre_de_la_obra')->nullable();
            $table->string('contrato_numero', 100)->nullable();
            $table->text('contratista')->nullable();
            
            // Status
            $table->text('status')->nullable();
            
            // Campos adicionales como texto para flexibilidad
            $table->text('avance_fisico')->nullable();
            $table->text('avance_financiero')->nullable();
            $table->text('total_pagado')->nullable();
            
            // Auditoría
            $table->string('fuente_datos', 50)->default('MANUAL');
            $table->string('archivo_origen', 255)->nullable();
            $table->boolean('validado')->default(false);
            
            // JSON para datos no estructurados
            $table->json('datos_adicionales')->nullable();
            
            $table->timestamps();
            
            // Índices básicos
            $table->index('folio1');
            $table->index('municipio');
            $table->index('validado');
        });
    }

    public function down()
    {
        Schema::dropIfExists('base_financiera');
    }
};