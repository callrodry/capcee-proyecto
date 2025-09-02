<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ArchivoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_subir_archivo_excel()
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();
        
        $archivo = UploadedFile::fake()->create('test.xlsx', 1000);
        
        $response = $this->actingAs($user)
            ->postJson('/api/archivos/upload', [
                'archivos' => [$archivo],
                'departamento_id' => $department->id
            ]);
        
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'archivos_subidos' => 1
            ]);
        
        $this->assertDatabaseHas('archivos_procesados', [
            'nombre_archivo' => 'test.xlsx',
            'departamento_id' => $department->id,
            'usuario_id' => $user->id
        ]);
    }
    
    public function test_valida_limite_diario_archivos()
    {
        $user = User::factory()->create();
        $department = Department::factory()->create([
            'limite_archivo_diario' => 1
        ]);
        
        // Crear un archivo existente para el día
        ArchivosProcesados::factory()->create([
            'departamento_id' => $department->id,
            'fecha_upload' => now()
        ]);
        
        $archivo = UploadedFile::fake()->create('test.xlsx', 1000);
        
        $response = $this->actingAs($user)
            ->postJson('/api/archivos/upload', [
                'archivos' => [$archivo],
                'departamento_id' => $department->id
            ]);
        
        $response->assertStatus(429)
            ->assertJson([
                'success' => false
            ]);
    }
}