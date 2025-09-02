<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseFinanciera extends Model
{
    use HasFactory;

    protected $table = 'base_financiera';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha' => 'date',
        'inicio_contrato' => 'date',
        'termino_contrato' => 'date',
        'fecha_factura' => 'date',
        'fecha_pago' => 'date',
        'fecha_economia_contrato' => 'date',
        'fecha_economia_finiquito' => 'date',
        'fecha_intereses' => 'date',
        'fecha_penalizacion' => 'date',
        'avance_financiero' => 'decimal:2',
        'avance_fisico' => 'decimal:2'
    ];

    // Relaciones
    public function archivoProcesado()
    {
        return $this->belongsTo(ArchivosProcesados::class, 'archivo_procesado_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Department::class, 'departamento_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scopes
    public function scopePorEstado($query, $estado)
    {
        return $query->where('status', $estado);
    }

    public function scopePorDepartamento($query, $departamentoId)
    {
        return $query->where('departamento_id', $departamentoId);
    }

    public function scopePorRangoFecha($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    // Métodos auxiliares
    public function calcularMontoEjercido()
    {
        return $this->total_autorizado_por_obra - $this->monto_por_ejercer_por_obra;
    }

    public function tieneAtraso()
    {
        return $this->termino_contrato < now() && $this->status != 'FINALIZADO';
    }
}