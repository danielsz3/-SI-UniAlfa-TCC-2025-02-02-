<?php

namespace App\Http\Controllers;

use App\Models\Ong;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class OngController extends Controller
{
    /**
     * Lista de ONGs (getList)
     * React Admin espera: { data: [...], total: number }
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('_limit', 10);
        $page = $request->input('_page', 1);
        $sort = $request->input('_sort', 'id_ong');
        $order = $request->input('_order', 'asc');
        $filter = json_decode($request->input('filter', '{}'), true);

        $query = Ong::query();

        // Filtros
        if (!empty($filter)) {
            foreach ($filter as $field => $value) {
                if ($value) {
                    $query->where($field, 'like', "%{$value}%");
                }
            }
        }

        // Ordenação
        $query->orderBy($sort, $order);

        $ongs = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json($ongs->items())
            ->header('X-Total-Count', $ongs->total())
            ->header('Access-Control-Expose-Headers', 'X-Total-Count');
    }

    /**
     * Listar ONGs incluindo as deletadas (admin)
     */
    public function indexWithTrashed(): JsonResponse
    {
        $ongs = Ong::withTrashed()->get();

        return response()->json([
            'data' => $ongs,
            'total' => $ongs->count()
        ]);
    }

    /**
     * Criar uma nova ONG (create)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_usuario' => 'required|exists:usuarios,id_usuario',
            'nome_ong' => 'required|string|max:255',
            'cnpj' => 'required|string|unique:ongs,cnpj|max:18',
            'descricao' => 'nullable|string',
            'url_logo' => 'nullable|string',
            'url_banner' => 'nullable|string',
            'telefone' => 'nullable|string|max:11',
            'pix' => 'nullable|string',
            'banco' => 'nullable|string',
            'agencia' => 'nullable|string',
            'conta' => 'nullable|string',
        ],[
            'id_usuario.exists' => 'O usuário especificado não existe.',
            'cnpj.unique' => 'O CNPJ já está em uso por outra ONG.',
            'cnpj.max' => 'O CNPJ não pode exceder 18 caracteres.',
            'telefone.max' => 'O telefone não pode exceder 11 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $ong = Ong::create($request->all());

        return response()->json(['data' => $ong], 201);
    }

    /**
     * Exibir uma ONG específica (getOne)
     */
    public function show($id): JsonResponse
    {
        $ong = Ong::find($id);

        if (!$ong) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }

        return response()->json(['data' => $ong]);
    }

    /**
     * Atualizar uma ONG (update)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $ong = Ong::find($id);

        if (!$ong) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_usuario' => 'sometimes|required|exists:usuarios,id_usuario',
            'nome_ong' => 'sometimes|required|string|max:255',
            'cnpj' => 'sometimes|required|string|unique:ongs,cnpj,' . $ong->id_ong . ',id_ong',
            'descricao' => 'nullable|string',
            'url_logo' => 'nullable|string',
            'url_banner' => 'nullable|string',
            'telefone' => 'nullable|string|max:11',
            'pix' => 'nullable|string',
            'banco' => 'nullable|string',
            'agencia' => 'nullable|string',
            'conta' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $ong->fill($request->all());
        $ong->save();

        return response()->json(['data' => $ong]);
    }

    /**
     * Deletar uma ONG (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        $ong = Ong::find($id);

        if (!$ong) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }

        $ong->delete();

        return response()->json(['data' => $ong]);
    }

    /**
     * Restaurar uma ONG deletada
     */
    public function restore($id): JsonResponse
    {
        $ong = Ong::withTrashed()->find($id);

        if (!$ong) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }

        $ong->restore();

        return response()->json(['data' => $ong]);
    }
}
