// database/migrations/2024_01_03_000001_add_foreign_keys.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Agregar clave foránea a users DESPUÉS de crear departments
        if (Schema::hasTable('users') && Schema::hasTable('departments')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'departamento_id')) {
                    $table->foreign('departamento_id')
                          ->references('id')
                          ->on('departments')
                          ->nullOnDelete();
                }
            });
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
        });
    }
};