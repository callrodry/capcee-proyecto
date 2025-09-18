// database/migrations/2024_01_01_000009_add_foreign_keys.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Foreign key para users -> departments
        if (!$this->foreignKeyExists('users', 'users_departamento_id_foreign')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('departamento_id')
                      ->references('id')
                      ->on('departments')
                      ->nullOnDelete();
            });
        }

        // 2. Foreign key para file_uploads -> users
        if (!$this->foreignKeyExists('file_uploads', 'file_uploads_usuario_id_foreign')) {
            Schema::table('file_uploads', function (Blueprint $table) {
                $table->foreign('usuario_id')
                      ->references('id')
                      ->on('users')
                      ->nullOnDelete();
            });
        }

        // 3. Foreign key para excel_staging -> file_uploads
        if (!$this->foreignKeyExists('excel_staging', 'excel_staging_upload_id_foreign')) {
            Schema::table('excel_staging', function (Blueprint $table) {
                $table->foreign('upload_id')
                      ->references('id')
                      ->on('file_uploads')
                      ->onDelete('cascade');
            });
        }

        // 4. Foreign key para archivos_procesados
        if (Schema::hasTable('archivos_procesados')) {
            if (!$this->foreignKeyExists('archivos_procesados', 'archivos_procesados_departamento_id_foreign')) {
                Schema::table('archivos_procesados', function (Blueprint $table) {
                    $table->foreign('departamento_id')
                          ->references('id')
                          ->on('departments')
                          ->onDelete('restrict');
                });
            }
            
            if (!$this->foreignKeyExists('archivos_procesados', 'archivos_procesados_usuario_id_foreign')) {
                Schema::table('archivos_procesados', function (Blueprint $table) {
                    $table->foreign('usuario_id')
                          ->references('id')
                          ->on('users')
                          ->onDelete('restrict');
                });
            }
        }

        // 5. Foreign key para asignaciones_de_campo
        if (Schema::hasTable('asignaciones_de_campo')) {
            if (!$this->foreignKeyExists('asignaciones_de_campo', 'asignaciones_de_campo_codigo_departamento_foreign')) {
                Schema::table('asignaciones_de_campo', function (Blueprint $table) {
                    $table->foreign('codigo_departamento')
                          ->references('code')
                          ->on('departments')
                          ->onDelete('cascade');
                });
            }
        }
    }

    public function down()
    {
        // Eliminar foreign keys si existen
        $this->dropForeignKeyIfExists('asignaciones_de_campo', 'asignaciones_de_campo_codigo_departamento_foreign');
        $this->dropForeignKeyIfExists('archivos_procesados', 'archivos_procesados_usuario_id_foreign');
        $this->dropForeignKeyIfExists('archivos_procesados', 'archivos_procesados_departamento_id_foreign');
        $this->dropForeignKeyIfExists('excel_staging', 'excel_staging_upload_id_foreign');
        $this->dropForeignKeyIfExists('file_uploads', 'file_uploads_usuario_id_foreign');
        $this->dropForeignKeyIfExists('users', 'users_departamento_id_foreign');
    }

    /**
     * Verificar si una foreign key existe
     */
    private function foreignKeyExists($table, $keyName)
    {
        $sql = "SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND CONSTRAINT_NAME = ?";
        
        $results = DB::select($sql, [$table, $keyName]);
        return count($results) > 0;
    }

    /**
     * Eliminar foreign key si existe
     */
    private function dropForeignKeyIfExists($table, $keyName)
    {
        if (Schema::hasTable($table) && $this->foreignKeyExists($table, $keyName)) {
            Schema::table($table, function (Blueprint $table) use ($keyName) {
                $table->dropForeign($keyName);
            });
        }
    }
};