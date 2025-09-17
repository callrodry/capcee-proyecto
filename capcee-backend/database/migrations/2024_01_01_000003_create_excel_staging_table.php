// database/migrations/2024_01_01_000003_create_excel_staging_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('excel_staging', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upload_id');
            $table->integer('row_number');
            $table->json('data_json');
            $table->string('departamento', 50)->nullable();
            $table->string('archivo_origen', 255);
            $table->boolean('procesado')->default(false);
            $table->text('errores')->nullable();
            $table->timestamps();
            
            // La foreign key la agregaremos después
            $table->index('upload_id');
            $table->index('procesado');
        });
    }

    public function down()
    {
        Schema::dropIfExists('excel_staging');
    }
};