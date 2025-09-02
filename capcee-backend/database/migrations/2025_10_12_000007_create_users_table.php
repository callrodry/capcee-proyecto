<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Solo agregar si no existe
            if (!Schema::hasColumn('users', 'departamento_id')) {
                $table->unsignedBigInteger('departamento_id')->nullable()->after('email');
                $table->foreign('departamento_id')->references('id')->on('departments')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropColumn('departamento_id');
        });
    }
};