<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivosProcesados extends Model
{
    use HasFactory;

    protected $table = 'archivos_procesados';

    protected $fillable = [
        'nombre_archivo',
        'tipo_archivo',
        'departamento_id',
        'usuario_id',
        'estado',
        'fecha_upload',
        'fecha_inicio_procesamiento',
        'fecha_fin_procesamiento',
        'registros_totales',
        'registros_exitosos',
        'registros_fallidos',
        'registros_duplicados',
        'errores',
        'observaciones',
        'ruta_archivo_original',
        'tamano_archivo_kb',
        'tiempo_procesamiento_segundos',
        'hash_archivo',
        'metadatos'
    ];

    protected $casts = [
        'fecha_upload' => 'datetime',
        'fecha_inicio_procesamiento' => 'datetime',
        'fecha_fin_procesamiento' => 'datetime',
        'metadatos' => 'array'
    ];

    // Estados constantes
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_CONVERTIDO = 'convertido';
    const ESTADO_VALIDADO = 'validado';
    const ESTADO_ERROR = 'error';

    // Relaciones
    public function departamento()
    {
        return $this->belongsTo(Department::class, 'departamento_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function registrosFinancieros()
    {
        return $this->hasMany(BaseFinanciera::class, 'archivo_procesado_id');
    }

    public function logs()
    {
        return $this->hasMany(LogActividad::class, 'archivo_procesado_id');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estado', self::ESTADO_EN_PROCESO);
    }

    public function scopeCompletados($query)
    {
        return $query->whereIn('estado', [self::ESTADO_CONVERTIDO, self::ESTADO_VALIDADO]);
    }

    public function scopeConErrores($query)
    {
        return $query->where('estado', self::ESTADO_ERROR);
    }

    // Métodos auxiliares
    public function cambiarEstado($nuevoEstado)
    {
        $this->estado = $nuevoEstado;
        
        if ($nuevoEstado == self::ESTADO_EN_PROCESO) {
            $this->fecha_inicio_procesamiento = now();
        } elseif (in_array($nuevoEstado, [self::ESTADO_CONVERTIDO, self::ESTADO_VALIDADO, self::ESTADO_ERROR])) {
            $this->fecha_fin_procesamiento = now();
            
            if ($this->fecha_inicio_procesamiento) {
                $this->tiempo_procesamiento_segundos = 
                    $this->fecha_fin_procesamiento->diffInSeconds($this->fecha_inicio_procesamiento);
            }
        }
        
        $this->save();
    }

    public function getPorcentajeExito()
    {
        if ($this->registros_totales == 0) {
            return 0;
        }
        
        return round(($this->registros_exitosos / $this->registros_totales) * 100, 2);
    }

    public function getTiempoProcesamiento()
    {
        if (!$this->tiempo_procesamiento_segundos) {
            return 'N/A';
        }
        
        $minutos = floor($this->tiempo_procesamiento_segundos / 60);
        $segundos = $this->tiempo_procesamiento_segundos % 60;
        
        return "{$minutos}m {$segundos}s";
    }
}