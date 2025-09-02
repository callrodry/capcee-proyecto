<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArchivosProcesados;
use App\Models\BaseFinanciera;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Obtener métricas generales del dashboard
     */
    public function metricas(Request $request)
    {
        $periodo = $request->get('periodo', 'hoy'); // hoy, semana, mes
        
        $query = ArchivosProcesados::query();
        
        // Aplicar filtro de periodo
        switch ($periodo) {
            case 'hoy':
                $query->whereDate('fecha_upload', today());
                break;
            case 'semana':
                $query->whereBetween('fecha_upload', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'mes':
                $query->whereMonth('fecha_upload', now()->month);
                break;
        }

        // Métricas generales
        $metricas = [
            'archivos_totales' => $query->count(),
            'archivos_procesados' => (clone $query)->completados()->count(),
            'archivos_pendientes' => (clone $query)->pendientes()->count(),
            'archivos_error' => (clone $query)->conErrores()->count(),
            'registros_totales' => BaseFinanciera::count(),
            'monto_total_autorizado' => BaseFinanciera::sum('total_autorizado_por_obra'),
            'monto_total_pagado' => BaseFinanciera::sum('total_pagado_por_obra'),
        ];

        // Archivos por estado
        $archivosPorEstado = (clone $query)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        // Archivos por departamento
        $archivosPorDepartamento = (clone $query)
            ->select('departamento_id', DB::raw('count(*) as total'))
            ->with('departamento:id,nombre')
            ->groupBy('departamento_id')
            ->get();

        // Tendencia diaria (últimos 7 días)
        $tendencia = ArchivosProcesados::select(
                DB::raw('DATE(fecha_upload) as fecha'),
                DB::raw('count(*) as total'),
                DB::raw('sum(registros_exitosos) as registros_procesados')
            )
            ->where('fecha_upload', '>=', now()->subDays(7))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'metricas' => $metricas,
                'archivos_por_estado' => $archivosPorEstado,
                'archivos_por_departamento' => $archivosPorDepartamento,
                'tendencia' => $tendencia
            ]
        ]);
    }

    /**
     * Obtener estadísticas por departamento
     */
    public function estadisticasDepartamento($departamentoId)
    {
        $departamento = Department::findOrFail($departamentoId);

        $estadisticas = [
            'nombre' => $departamento->nombre,
            'archivos_hoy' => $departamento->archivosHoy(),
            'limite_diario' => $departamento->limite_archivo_diario,
            'archivos_restantes' => $departamento->archivosRestantesHoy(),
            'total_archivos_mes' => $departamento->archivos()
                ->whereMonth('fecha_upload', now()->month)
                ->count(),
            'registros_totales' => BaseFinanciera::where('departamento_id', $departamentoId)->count(),
            'ultimos_archivos' => $departamento->archivos()
                ->with('usuario:id,name')
                ->orderBy('fecha_upload', 'desc')
                ->limit(5)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $estadisticas
        ]);
    }
}