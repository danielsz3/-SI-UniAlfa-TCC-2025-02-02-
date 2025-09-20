<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    /**
     * Lista de usuários (getList)
     * React Admin espera: { data: [...], total: number }
     */
    public function index(Request $request): JsonResponse
{
    // paginação estilo jsonServer
    $start = (int) $request->query('_start', 0);
    $end   = (int) $request->query('_end', 10);
    $perPage = ($end - $start) > 0 ? ($end - $start) : 10;
    $page    = intval($start / $perPage) + 1;

    // ordenação
    $sort  = $request->query('_sort', 'id');
    $order = $request->query('_order', 'ASC');

    $query = Usuario::query();

    // 🚀 aplica todos os filtros vindos pela URL
    foreach ($request->query() as $field => $value) {
        // ignora parametros reservados do React-Admin
        if (in_array($field, ['_start','_end','_sort','_order','page','perPage'])) {
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

        // 🔎 campos textuais usam LIKE
        if (in_array($field, ['nome','email','telefone'])) {
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
}
    /**
     * Listar usuários incluindo os deletados (admin)
     */
    public function indexWithTrashed(): JsonResponse
    {
        $usuarios = Usuario::withTrashed()->get();

        return response()->json([
            'data' => $usuarios,
            'total' => $usuarios->count()
        ]);
    }

    /**
     * Criar um novo usuário (create)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|min:8|confirmed',
            'cpf' => 'required|string|unique:usuarios,cpf',
            'data_nascimento' => 'required|date|before:today',
            'telefone' => 'nullable|string',
            'role' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $usuario = Usuario::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'cpf' => $request->cpf,
            'data_nascimento' => $request->data_nascimento,
            'telefone' => $request->telefone ?? null,
            'role' => $request->role ?? 'user',
        ]);

        return response()->json($usuario, 201);
    }

    /**
     * Exibir um usuário específico (getOne)
     */
    public function show($id): JsonResponse
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        return response()->json($usuario);
    }

    /**
     * Atualizar um usuário (update)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nome' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:usuarios,email,' . $usuario->id_usuario . ',id_usuario',
            'password' => 'sometimes|required|min:8|confirmed',
            'cpf' => 'sometimes|required|string|unique:usuarios,cpf,' . $usuario->id_usuario . ',id_usuario',
            'data_nascimento' => 'sometimes|required|date|before:today',
            'telefone' => 'nullable|string',
            'role' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        if ($request->filled('nome')) $usuario->nome = $request->nome;
        if ($request->filled('email')) $usuario->email = $request->email;
        if ($request->filled('password')) $usuario->password = bcrypt($request->password);
        if ($request->filled('cpf')) $usuario->cpf = $request->cpf;
        if ($request->filled('data_nascimento')) $usuario->data_nascimento = $request->data_nascimento;
        if ($request->filled('telefone')) $usuario->telefone = $request->telefone;
        if ($request->filled('role')) $usuario->role = $request->role;

        $usuario->save();

        return response()->json(['data' => $usuario]);
    }

    /**
     * Deletar um usuário (delete / soft delete)
     */
    public function destroy($id): JsonResponse
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        $usuario->delete();

        return response()->json(['data' => $usuario]);
    }

    /**
     * Restaurar um usuário deletado
     */
    public function restore($id): JsonResponse
    {
        $usuario = Usuario::withTrashed()->find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        $usuario->restore();

        return response()->json(['data' => $usuario]);
    }
}