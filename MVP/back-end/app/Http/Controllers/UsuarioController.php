<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Traits\SearchIndex;

class UsuarioController extends Controller
{
    use SearchIndex;

    /**
     * Lista de usuários (getList)
     * React Admin espera: { data: [...], total: number }
     */
    public function index(Request $request): JsonResponse
    {
        return $this->SearchIndex(
            $request,
            Usuario::query(),
            'usuarios',
            ['nome', 'email', 'telefone']
        );
    }

    /**
     * Criar um novo usuário
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|min:2|max:150',
            'email' => 'required|email|max:150|unique:usuarios,email',
            'password' => 'required|min:8|confirmed',
            'cpf' => 'required|string|size:11|regex:/^[0-9]+$/|unique:usuarios,cpf',
            'data_nascimento' => 'required|date|before:today|after:1900-01-01',
            'telefone' => 'nullable|string|size:11|regex:/^[0-9]+$/',
            'role' => 'nullable|string|in:user,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $usuario = Usuario::create([
                'nome' => $request->nome,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'cpf' => $request->cpf,
                'data_nascimento' => $request->data_nascimento,
                'telefone' => $request->telefone,
                'role' => $request->role ?? 'user',
            ]);

            return response()->json($usuario, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível criar o usuário'], 500);
        }
    }

    /**
     * Exibir um usuário específico
     */
    public function show($id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);

            if (!$usuario) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            return response()->json($usuario, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível carregar o usuário'], 500);
        }
    }

    /**
     * Atualizar um usuário
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);

            if (!$usuario) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'nome' => 'sometimes|required|string|min:2|max:150',
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    'max:150',
                    Rule::unique('usuarios')->ignore($usuario->id)
                ],
                'password' => 'sometimes|required|min:8|confirmed',
                'cpf' => [
                    'sometimes',
                    'required',
                    'string',
                    'size:11',
                    'regex:/^[0-9]+$/',
                    Rule::unique('usuarios')->ignore($usuario->id)
                ],
                'data_nascimento' => 'sometimes|required|date|before:today|after:1900-01-01',
                'telefone' => 'nullable|string|size:11|regex:/^[0-9]+$/',
                'role' => 'nullable|string|in:user,admin',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->only([
                'nome',
                'email',
                'cpf',
                'data_nascimento',
                'telefone',
                'role'
            ]);

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $usuario->update($data);

            return response()->json($usuario->fresh(), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível atualizar o usuário'], 500);
        }
    }

    /**
     * Deletar um usuário
     */
    public function destroy($id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);

            if (!$usuario) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            $usuario->delete();

            return response()->json(null, 204); // apenas status code
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível excluir o usuário'], 500);
        }
    }

    /**
     * Restaurar um usuário deletado
     */
    public function restore($id): JsonResponse
    {
        try {
            $usuario = Usuario::withTrashed()->find($id);

            if (!$usuario) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            if (!$usuario->trashed()) {
                return response()->json(['error' => 'Usuário já está ativo'], 400);
            }

            $usuario->restore();

            return response()->json($usuario, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível restaurar o usuário'], 500);
        }
    }
}