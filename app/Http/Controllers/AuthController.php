<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** POST /api/register */
    public function register(RegisterRequest $request): JsonResponse
    {
        // A senha é criptografada pelo cast "hashed" do model User
        $user = User::create($request->validated());

        return response()->json([
            'message' => 'Usuário cadastrado com sucesso.',
            'user' => new UserResource($user),
            'token' => $user->createToken('api')->plainTextToken,
        ], 201);
    }

    /** POST /api/login */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'E-mail ou senha inválidos.'], 401);
        }

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'user' => new UserResource($user),
            'token' => $user->createToken('api')->plainTextToken,
        ]);
    }

    /** GET /api/me */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /** POST /api/logout */
    public function logout(Request $request): JsonResponse
    {
        // Revoga apenas o token usado nesta requisição
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
