<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->integer('limite_archivo_diario')->default(100);
            $table->integer('tamano_archivo_maximo_mb')->default(50);
            $table->boolean('esta_activo')->default(true);
            $table->timestamps();
            
            $table->index('code');
            $table->index('esta_activo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('departments');
    }
};