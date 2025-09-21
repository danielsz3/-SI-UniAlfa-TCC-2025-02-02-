<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Lista de usuários (getList)
     * React Admin espera: { data: [...], total: number }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // paginação estilo jsonServer
            $start = (int) $request->query('_start', 0);
            $end   = (int) $request->query('_end', 10);
            $perPage = ($end - $start) > 0 ? ($end - $start) : 10;
            $page    = intval($start / $perPage) + 1;

            // ordenação
            $sort  = $request->query('_sort', 'id');
            $order = $request->query('_order', 'ASC');

            $query = Usuario::query();

            // aplica todos os filtros vindos pela URL
            foreach ($request->query() as $field => $value) {
                // ignora parametros reservados do React-Admin
                if (in_array($field, ['_start', '_end', '_sort', '_order', 'page', 'perPage'])) {
                    continue;
                }

                if ($value === null || $value === '') continue;

                // 🔎  suporte a ranges -> campo_from & campo_to
                if (preg_match('/(.+)_from$/', $field, $matches)) {
                    $column = $matches[1];
                    $query->where($column, '>=', $value);
                    continue;
                }
                if (preg_match('/(.+)_to$/', $field, $matches)) {
                    $column = $matches[1];
                    $query->where($column, '<=', $value);
                    continue;
                }

                // campos textuais usam LIKE
                if (in_array($field, ['nome', 'email', 'telefone'])) {
                    $query->where($field, 'like', '%' . $value . '%');
                } else {
                    // demais = comparação exata
                    $query->where($field, $value);
                }
            }

            // ordenação
            $query->orderBy($sort, $order);

            $usuarios = $query->paginate($perPage, ['*'], 'page', $page);

            return response()
                ->json($usuarios->items())
                ->header('X-Total-Count', $usuarios->total())
                ->header('Access-Control-Expose-Headers', 'X-Total-Count');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar a lista de usuários'
            ], 500);
        }
    }

    /**
     * Criar um novo usuário (create)
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
        ], [
            // Mensagens personalizadas
            'nome.required' => 'O nome é obrigatório',
            'nome.min' => 'O nome deve ter pelo menos 2 caracteres',
            'nome.max' => 'O nome não pode ter mais de 150 caracteres',

            'email.required' => 'O email é obrigatório',
            'email.email' => 'Digite um email válido',
            'email.unique' => 'Este email já está sendo usado por outro usuário',
            'email.max' => 'O email não pode ter mais de 150 caracteres',

            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres',
            'password.confirmed' => 'A confirmação da senha não confere',

            'cpf.required' => 'O CPF é obrigatório',
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos',
            'cpf.regex' => 'O CPF deve conter apenas números',
            'cpf.unique' => 'Este CPF já está cadastrado',

            'data_nascimento.required' => 'A data de nascimento é obrigatória',
            'data_nascimento.date' => 'Digite uma data válida',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje',
            'data_nascimento.after' => 'Digite uma data de nascimento válida',

            'telefone.size' => 'O telefone deve ter exatamente 11 dígitos',
            'telefone.regex' => 'O telefone deve conter apenas números',

            'role.in' => 'O tipo de usuário deve ser "user" ou "admin"',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Dados inválidos',
                'message' => 'Verifique os campos e tente novamente',
                'errors' => $validator->errors()
            ], 422);
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

            return response()->json([
                'message' => 'Usuário criado com sucesso!',
                'data' => $usuario
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível criar o usuário'
            ], 500);
        }
    }

    /**
     * Exibir um usuário específico (getOne)
     */
    public function show($id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);

            if (!$usuario) {
                return response()->json([
                    'error' => 'Usuário não encontrado',
                    'message' => 'O usuário solicitado não existe'
                ], 404);
            }

            return response()->json([
                'message' => 'Usuário encontrado',
                'data' => $usuario
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar o usuário'
            ], 500);
        }
    }

    /**
     * Atualizar um usuário (update)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);

            if (!$usuario) {
                return response()->json([
                    'error' => 'Usuário não encontrado',
                    'message' => 'O usuário que você está tentando atualizar não existe'
                ], 404);
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
            ], [
                // Mensagens personalizadas (mesmas do store)
                'nome.required' => 'O nome é obrigatório',
                'nome.min' => 'O nome deve ter pelo menos 2 caracteres',
                'nome.max' => 'O nome não pode ter mais de 150 caracteres',

                'email.required' => 'O email é obrigatório',
                'email.email' => 'Digite um email válido',
                'email.unique' => 'Este email já está sendo usado por outro usuário',
                'email.max' => 'O email não pode ter mais de 150 caracteres',

                'password.required' => 'A senha é obrigatória',
                'password.min' => 'A senha deve ter pelo menos 8 caracteres',
                'password.confirmed' => 'A confirmação da senha não confere',

                'cpf.required' => 'O CPF é obrigatório',
                'cpf.size' => 'O CPF deve ter exatamente 11 dígitos',
                'cpf.regex' => 'O CPF deve conter apenas números',
                'cpf.unique' => 'Este CPF já está cadastrado',

                'data_nascimento.required' => 'A data de nascimento é obrigatória',
                'data_nascimento.date' => 'Digite uma data válida',
                'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje',
                'data_nascimento.after' => 'Digite uma data de nascimento válida',

                'telefone.size' => 'O telefone deve ter exatamente 11 dígitos',
                'telefone.regex' => 'O telefone deve conter apenas números',

                'role.in' => 'O tipo de usuário deve ser "user" ou "admin"',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dados inválidos',
                    'message' => 'Verifique os campos e tente novamente',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Atualização refatorada - mais limpa
            $data = $request->only([
                'nome',
                'email',
                'cpf',
                'data_nascimento',
                'telefone',
                'role'
            ]);

            // Password precisa de tratamento especial
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $usuario->update($data);

            return response()->json([
                'message' => 'Usuário atualizado com sucesso!',
                'data' => $usuario->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível atualizar o usuário'
            ], 500);
        }
    }

    /**
     * Deletar um usuário (delete / soft delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);

            if (!$usuario) {
                return response()->json([
                    'error' => 'Usuário não encontrado',
                    'message' => 'O usuário que você está tentando excluir não existe'
                ], 404);
            }

            $usuario->delete();

            return response()->json([
                'message' => 'Usuário excluído com sucesso!',
                'data' => $usuario
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível excluir o usuário'
            ], 500);
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
                return response()->json([
                    'error' => 'Usuário não encontrado',
                    'message' => 'O usuário que você está tentando restaurar não existe'
                ], 404);
            }

            if (!$usuario->trashed()) {
                return response()->json([
                    'error' => 'Usuário já está ativo',
                    'message' => 'Este usuário não precisa ser restaurado'
                ], 400);
            }

            $usuario->restore();

            return response()->json([
                'message' => 'Usuário restaurado com sucesso!',
                'data' => $usuario
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível restaurar o usuário'
            ], 500);
        }
    }
}
