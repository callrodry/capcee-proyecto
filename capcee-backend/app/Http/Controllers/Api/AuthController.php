<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        Log::info('Login attempt for: ' . $request->email);
        log::info($request);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar usuario
        $user = User::where('email', $request->email)->first();
        
        // Verificar usuario y contraseña
        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::error('Login failed: Invalid credentials');
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }
        
        // Cargar departamento si existe
        if (method_exists($user, 'departamento')) {
            $user->load('departamento');
        }
        
        // Crear token
        $token = $user->createToken('auth-token')->plainTextToken;
        
        Log::info('Login successful for: ' . $user->email);

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'departamento' => $user->departamento ?? null
            ]
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        
        if ($user && method_exists($user, 'departamento')) {
            $user->load('departamento');
        }
        
        return response()->json($user);
    }
}