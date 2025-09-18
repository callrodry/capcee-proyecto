// database/migrations/2014_10_12_000000_create_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('departamento_id')->nullable(); // Campo para departamento
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('departamento_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};