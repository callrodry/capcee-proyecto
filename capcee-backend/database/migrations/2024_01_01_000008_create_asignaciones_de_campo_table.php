<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asignaciones_de_campos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_departamento', 20);
            $table->string('file_type', 50); // SEGUIMIENTO_PAGOS, OBRAS_2025, etc
            $table->string('excel_column', 100);
            $table->string('campo_de_base_de_datos', 100);
            $table->enum('data_type', ['string', 'number', 'date', 'boolean']);
            $table->boolean('is_required')->default(false);
            $table->json('validacion_de_reglas')->nullable();
            $table->json('transformacion_de_reglas')->nullable();
            $table->boolean('esta_activo')->default(true);
            $table->timestamps();
            
            // Foreign key
            $table->foreign('codigo_departamento')->references('code')->on('departments');
            
            // Índice único
            $table->unique(['codigo_departamento', 'file_type', 'excel_column'], 'unique_mapping');
            $table->index(['codigo_departamento', 'file_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('asignaciones_de_campos');
    }
};