<?php

namespace App\Services;

use App\Models\ArchivosProcesados;
use App\Models\BaseFinanciera;
use App\Models\AsignacionesCampos;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ExcelProcessor
{
    protected $archivo;
    protected $mapeoColumnas;
    protected $errores = [];
    protected $registrosProcesados = 0;
    protected $registrosExitosos = 0;
    protected $registrosFallidos = 0;
    protected $registrosDuplicados = 0;

    public function __construct(ArchivosProcesados $archivo)
    {
        $this->archivo = $archivo;
        $this->cargarMapeoColumnas();
    }

    /**
     * Procesar archivo Excel completo
     */
    public function procesar()
    {
        try {
            $this->archivo->cambiarEstado(ArchivosProcesados::ESTADO_EN_PROCESO);
            
            // Validar estructura antes de procesar
            if (!$this->validarEstructura()) {
                throw new Exception('La estructura del archivo no es válida');
            }

            // Procesar archivo por chunks para optimizar memoria
            $this->procesarPorChunks();

            // Actualizar estadísticas finales
            $this->actualizarEstadisticas();

            // Cambiar estado según resultados
            if ($this->registrosFallidos == 0) {
                $this->archivo->cambiarEstado(ArchivosProcesados::ESTADO_CONVERTIDO);
            } else {
                $this->archivo->cambiarEstado(ArchivosProcesados::ESTADO_VALIDADO);
            }

            return true;

        } catch (Exception $e) {
            $this->errores[] = $e->getMessage();
            $this->archivo->errores = json_encode($this->errores);
            $this->archivo->cambiarEstado(ArchivosProcesados::ESTADO_ERROR);
            
            Log::error('Error procesando archivo: ' . $e->getMessage(), [
                'archivo_id' => $this->archivo->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Validar estructura del archivo Excel
     */
    protected function validarEstructura()
    {
        try {
            $data = Excel::toArray(null, storage_path('app/' . $this->archivo->ruta_archivo_original));
            
            if (empty($data) || empty($data[0])) {
                $this->errores[] = 'El archivo está vacío';
                return false;
            }

            $headers = array_map('strtoupper', array_map('trim', $data[0][0] ?? []));
            
            // Verificar columnas requeridas según tipo de archivo
            $columnasRequeridas = $this->obtenerColumnasRequeridas();
            $columnasFaltantes = array_diff($columnasRequeridas, $headers);
            
            if (!empty($columnasFaltantes)) {
                $this->errores[] = 'Columnas faltantes: ' . implode(', ', $columnasFaltantes);
                return false;
            }

            return true;

        } catch (Exception $e) {
            $this->errores[] = 'Error validando estructura: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Procesar archivo por chunks
     */
    protected function procesarPorChunks()
    {
        $chunkSize = config('app.chunk_size', 1000);
        
        Excel::filter('chunk')->load(
            storage_path('app/' . $this->archivo->ruta_archivo_original)
        )->chunk($chunkSize, function($results) {
            foreach ($results as $row) {
                $this->procesarFila($row);
            }
        });
    }

    /**
     * Procesar una fila individual
     */
    protected function procesarFila($row)
    {
        $this->registrosProcesados++;
        
        DB::beginTransaction();
        try {
            // Mapear datos según configuración
            $datosMapeados = $this->mapearDatos($row);
            
            // Validar datos
            if (!$this->validarDatos($datosMapeados)) {
                $this->registrosFallidos++;
                DB::rollBack();
                return;
            }
            
            // Verificar duplicados
            if ($this->esDuplicado($datosMapeados)) {
                $this->registrosDuplicados++;
                DB::rollBack();
                return;
            }
            
            // Guardar en base de datos
            $registro = BaseFinanciera::create(array_merge($datosMapeados, [
                'archivo_procesado_id' => $this->archivo->id,
                'departamento_id' => $this->archivo->departamento_id,
                'usuario_id' => $this->archivo->usuario_id,
            ]));
            
            $this->registrosExitosos++;
            DB::commit();
            
        } catch (Exception $e) {
            DB::rollBack();
            $this->registrosFallidos++;
            $this->errores[] = "Error en fila {$this->registrosProcesados}: " . $e->getMessage();
        }
    }

    /**
     * Mapear datos del Excel a campos de base de datos
     */
    protected function mapearDatos($row)
    {
        $datosMapeados = [];
        
        foreach ($this->mapeoColumnas as $mapeo) {
            $valorExcel = $row[$mapeo->excel_column] ?? null;
            
            // Aplicar transformaciones si existen
            if ($mapeo->transformacion_de_reglas) {
                $valorExcel = $this->aplicarTransformacion(
                    $valorExcel, 
                    json_decode($mapeo->transformacion_de_reglas, true)
                );
            }
            
            // Convertir tipo de dato
            $valorConvertido = $this->convertirTipoDato(
                $valorExcel, 
                $mapeo->data_type
            );
            
            $datosMapeados[$mapeo->campo_de_base_de_datos] = $valorConvertido;
        }
        
        return $datosMapeados;
    }

    /**
     * Validar datos mapeados
     */
    protected function validarDatos($datos)
    {
        foreach ($this->mapeoColumnas as $mapeo) {
            if ($mapeo->is_required && empty($datos[$mapeo->campo_de_base_de_datos])) {
                $this->errores[] = "Campo requerido vacío: {$mapeo->campo_de_base_de_datos}";
                return false;
            }
            
            // Aplicar reglas de validación personalizadas
            if ($mapeo->validacion_de_reglas) {
                $reglas = json_decode($mapeo->validacion_de_reglas, true);
                if (!$this->aplicarValidacion($datos[$mapeo->campo_de_base_de_datos], $reglas)) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Verificar si el registro es duplicado
     */
    protected function esDuplicado($datos)
    {
        // Verificar por folio si existe
        if (isset($datos['folio1'])) {
            return BaseFinanciera::where('folio1', $datos['folio1'])
                ->where('departamento_id', $this->archivo->departamento_id)
                ->exists();
        }
        
        return false;
    }

    /**
     * Actualizar estadísticas del archivo
     */
    protected function actualizarEstadisticas()
    {
        $this->archivo->update([
            'registros_totales' => $this->registrosProcesados,
            'registros_exitosos' => $this->registrosExitosos,
            'registros_fallidos' => $this->registrosFallidos,
            'registros_duplicados' => $this->registrosDuplicados,
            'errores' => !empty($this->errores) ? json_encode($this->errores) : null
        ]);
    }

    /**
     * Cargar mapeo de columnas desde base de datos
     */
    protected function cargarMapeoColumnas()
    {
        $tipoArchivo = $this->detectarTipoArchivo();
        
        $this->mapeoColumnas = AsignacionesCampos::where('codigo_departamento', $this->archivo->departamento->code)
            ->where('file_type', $tipoArchivo)
            ->where('esta_activo', true)
            ->get();
    }

    /**
     * Detectar tipo de archivo basado en nombre o contenido
     */
    protected function detectarTipoArchivo()
    {
        $nombreArchivo = strtoupper($this->archivo->nombre_archivo);
        
        if (str_contains($nombreArchivo, 'SEGUIMIENTO') && str_contains($nombreArchivo, 'PAGOS')) {
            return 'SEGUIMIENTO_PAGOS';
        } elseif (str_contains($nombreArchivo, 'OBRAS') && str_contains($nombreArchivo, '2025')) {
            return 'OBRAS_2025';
        } elseif (str_contains($nombreArchivo, 'BANORTE')) {
            return 'PAGOS_BANORTE';
        }
        
        return 'GENERAL';
    }

    /**
     * Obtener columnas requeridas según tipo
     */
    protected function obtenerColumnasRequeridas()
    {
        $tipo = $this->detectarTipoArchivo();
        
        $columnasBase = [];
        
        switch ($tipo) {
            case 'SEGUIMIENTO_PAGOS':
                $columnasBase = ['NÚMERO', 'ESTADO', 'FECHA', 'FOLIO', 'IMPORTE'];
                break;
            case 'OBRAS_2025':
                $columnasBase = ['FOLIO1', 'PARTIDA', 'OBRA', 'MUNICIPIO'];
                break;
            case 'PAGOS_BANORTE':
                $columnasBase = ['MES', 'BANCO', 'BENEFICIARIO', 'RFC'];
                break;
        }
        
        return array_map('strtoupper', $columnasBase);
    }

    /**
     * Convertir tipo de dato
     */
    protected function convertirTipoDato($valor, $tipo)
    {
        if (is_null($valor) || $valor === '') {
            return null;
        }
        
        switch ($tipo) {
            case 'number':
                return is_numeric($valor) ? floatval($valor) : null;
            case 'date':
                try {
                    return \Carbon\Carbon::parse($valor)->format('Y-m-d');
                } catch (Exception $e) {
                    return null;
                }
            case 'boolean':
                return filter_var($valor, FILTER_VALIDATE_BOOLEAN);
            default:
                return trim(strval($valor));
        }
    }

    /**
     * Aplicar transformación a un valor
     */
    protected function aplicarTransformacion($valor, $reglas)
    {
        if (isset($reglas['uppercase']) && $reglas['uppercase']) {
            $valor = strtoupper($valor);
        }
        
        if (isset($reglas['lowercase']) && $reglas['lowercase']) {
            $valor = strtolower($valor);
        }
        
        if (isset($reglas['trim']) && $reglas['trim']) {
            $valor = trim($valor);
        }
        
        if (isset($reglas['replace']) && is_array($reglas['replace'])) {
            foreach ($reglas['replace'] as $buscar => $reemplazar) {
                $valor = str_replace($buscar, $reemplazar, $valor);
            }
        }
        
        return $valor;
    }

    /**
     * Aplicar validación personalizada
     */
    protected function aplicarValidacion($valor, $reglas)
    {
        if (isset($reglas['min']) && $valor < $reglas['min']) {
            $this->errores[] = "Valor menor al mínimo permitido: {$valor} < {$reglas['min']}";
            return false;
        }
        
        if (isset($reglas['max']) && $valor > $reglas['max']) {
            $this->errores[] = "Valor mayor al máximo permitido: {$valor} > {$reglas['max']}";
            return false;
        }
        
        if (isset($reglas['pattern']) && !preg_match($reglas['pattern'], $valor)) {
            $this->errores[] = "Valor no cumple con el patrón requerido: {$valor}";
            return false;
        }
        
        return true;
    }
}