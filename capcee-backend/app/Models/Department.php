<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'nombre',
        'descripcion',
        'limite_archivo_diario',
        'tamano_archivo_maximo_mb',
        'esta_activo'
    ];

    protected $casts = [
        'esta_activo' => 'boolean'
    ];

    // Relaciones
    public function archivos()
    {
        return $this->hasMany(ArchivosProcesados::class, 'departamento_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_departamentos');
    }

    public function asignacionesCampos()
    {
        return $this->hasMany(AsignacionesCampos::class, 'codigo_departamento', 'code');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('esta_activo', true);
    }

    // Métodos auxiliares
    public function archivosHoy()
    {
        return $this->archivos()
            ->whereDate('fecha_upload', today())
            ->count();
    }

    public function puedeSubirArchivos()
    {
        return $this->archivosHoy() < $this->limite_archivo_diario;
    }

    public function archivosRestantesHoy()
    {
        return max(0, $this->limite_archivo_diario - $this->archivosHoy());
    }
}