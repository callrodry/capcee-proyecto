<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('base_financiera', function (Blueprint $table) {
            // ===== CAMPOS PRINCIPALES =====
            $table->id();
            $table->decimal('folio1', 10, 0)->nullable()->comment('Número de folio principal');
            $table->text('partida')->nullable()->comment('Descripción de la partida presupuestal');
            $table->string('cct', 50)->nullable()->comment('Clave de Centro de Trabajo');
            $table->decimal('folio2', 10, 0)->nullable()->comment('Folio secundario');
            $table->text('programa')->nullable()->comment('Nombre del programa presupuestal');
            
            // ===== INFORMACIÓN DEL PROYECTO =====
            $table->text('obra')->nullable()->comment('Descripción de la obra');
            $table->string('municipio', 100)->nullable()->comment('Municipio donde se ejecuta');
            $table->string('localidad', 100)->nullable()->comment('Localidad específica');
            $table->date('inicio_contrato')->nullable()->comment('Fecha de inicio del contrato');
            $table->date('termino_contrato')->nullable()->comment('Fecha de término del contrato');
            $table->string('contrato', 100)->nullable()->comment('Número de contrato');
            
            // ===== DATOS DE LA EMPRESA =====
            $table->text('empresa')->nullable()->comment('Nombre de la empresa contratista');
            $table->string('rfc', 20)->nullable()->comment('RFC de la empresa');
            $table->text('representante_legal')->nullable()->comment('Nombre del representante legal');
            $table->string('telefono_contacto', 20)->nullable()->comment('Teléfono de contacto');
            
            // ===== RECURSOS AUTORIZADOS 2024 =====
            $table->decimal('original_estatal_2024', 15, 2)->nullable();
            $table->decimal('otros_recursos_2024', 15, 2)->nullable();
            $table->decimal('municipal_2024_original', 15, 2)->nullable();
            $table->decimal('original_incentivos_derivados_de_la_colaboracion_fiscal', 15, 2)->nullable();
            $table->decimal('fafef_2024', 15, 2)->nullable();
            $table->decimal('fise_2024', 15, 2)->nullable();
            $table->decimal('participaciones_2024', 15, 2)->nullable();
            $table->decimal('fam_basico_2024', 15, 2)->nullable();
            $table->decimal('remanente_fam_basico_2024', 15, 2)->nullable();
            $table->decimal('reduccion', 15, 2)->nullable();
            $table->decimal('fam_media_superior_2024', 15, 2)->nullable();
            $table->decimal('remanente_fam_media_superior_2024', 15, 2)->nullable();
            $table->decimal('fam_superior_2024', 15, 2)->nullable();
            $table->decimal('remanente_fam_superior_2024', 15, 2)->nullable();
            $table->decimal('total_autorizado_por_programa', 15, 2)->nullable();
            $table->decimal('total_autorizado_por_obra', 15, 2)->nullable();
            
            // ===== RECURSOS CONTRATADOS =====
            $table->decimal('estatal_2024', 15, 2)->nullable();
            $table->decimal('otros_recursos_2024_1', 15, 2)->nullable();
            $table->decimal('municipal_2024', 15, 2)->nullable();
            $table->decimal('incentivos_derivados_de_la_colaboracion_fiscal', 15, 2)->nullable();
            $table->decimal('fafef_2024_1', 15, 2)->nullable();
            $table->decimal('participaciones_2024_1', 15, 2)->nullable();
            $table->decimal('fise_2024_1', 15, 2)->nullable();
            $table->decimal('fam_basico_2024_1', 15, 2)->nullable();
            $table->decimal('remanente_fam_basico_2024_1', 15, 2)->nullable();
            $table->decimal('fam_media_superior_2024_1', 15, 2)->nullable();
            $table->decimal('remanente_fam_media_superior_2024_1', 15, 2)->nullable();
            $table->decimal('fam_superior_2024_1', 15, 2)->nullable();
            $table->decimal('remanente_fam_superior_2024_1', 15, 2)->nullable();
            $table->decimal('total_contratado_por_programa', 15, 2)->nullable();
            $table->decimal('total_contratado_por_obra', 15, 2)->nullable();
            
            // ===== INFORMACIÓN DE PAGOS =====
            $table->string('talon', 50)->nullable()->comment('Número de talón de pago');
            $table->date('fecha')->nullable()->comment('Fecha del pago');
            $table->decimal('anticipo', 15, 2)->nullable();
            $table->string('factura', 100)->nullable();
            $table->date('fecha_factura')->nullable();
            $table->string('folio_fiscal', 100)->nullable();
            $table->date('fecha_pago')->nullable();
            
            // ===== ESTIMACIONES (1-5) =====
            for ($i = 1; $i <= 5; $i++) {
                $suffix = $i == 1 ? '' : '_' . ($i - 1);
                $table->decimal('estimacion' . ($i == 1 ? '_1' : '_' . $i), 15, 2)->nullable();
                $table->string('factura' . ($i == 1 ? '_1' : '_' . $i), 100)->nullable();
                $table->date('fecha_factura' . ($i == 1 ? '_1' : '_' . $i))->nullable();
                $table->string('folio_fiscal' . ($i == 1 ? '_1' : '_' . $i), 100)->nullable();
                $table->date('fecha_pago' . ($i == 1 ? '_1' : '_' . $i))->nullable();
            }
            
            // ===== CONTROLES FINANCIEROS =====
            $table->decimal('retencion_del_5_al_millar', 15, 2)->nullable();
            $table->decimal('total_pagado_por_partida', 15, 2)->nullable();
            $table->decimal('total_pagado_por_obra', 15, 2)->nullable();
            $table->decimal('monto_por_ejercer_por_partida', 15, 2)->nullable();
            $table->decimal('monto_por_ejercer_por_obra', 15, 2)->nullable();
            
            // ===== ESTADO Y AVANCES =====
            $table->enum('status', ['AUTORIZADO', 'EN_PROCESO', 'PAGADO', 'FINALIZADO', 'CANCELADO', 'PENDIENTE'])
                  ->default('PENDIENTE');
            $table->decimal('avance_financiero', 5, 2)->nullable();
            $table->decimal('avance_fisico', 5, 2)->nullable();
            $table->decimal('reintegro', 15, 2)->nullable();
            $table->decimal('total_pagado_obra_y_reintegro', 15, 2)->nullable();
            $table->decimal('saldo_del_contrato', 15, 2)->nullable();
            
            // ===== ECONOMÍAS Y AJUSTES =====
            $table->decimal('economia_de_contrato', 15, 2)->nullable();
            $table->string('folio_siaf_1', 50)->nullable();
            $table->date('fecha_economia_contrato')->nullable();
            $table->decimal('economia_de_finiquito', 15, 2)->nullable();
            $table->date('fecha_economia_finiquito')->nullable();
            $table->decimal('intereses', 15, 2)->nullable();
            $table->date('fecha_intereses')->nullable();
            $table->decimal('penalizacion', 15, 2)->nullable();
            $table->date('fecha_penalizacion')->nullable();
            $table->decimal('5_al_millar', 15, 2)->nullable();
            
            // ===== OBSERVACIONES Y METADATA =====
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('archivo_procesado_id')->nullable();
            $table->unsignedBigInteger('departamento_id')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index('folio1');
            $table->index('folio2');
            $table->index('status');
            $table->index('fecha');
            $table->index('municipio');
            $table->index('departamento_id');
            $table->index('archivo_procesado_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('base_financiera');
    }
};