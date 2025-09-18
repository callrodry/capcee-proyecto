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
}