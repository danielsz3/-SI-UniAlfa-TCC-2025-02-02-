<?php

namespace App\Http\Controllers;

use App\Models\ContatoOng;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ContatoOngController extends Controller
{
    /**
     * Lista de contatos (paginada para React Admin)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('_limit', 10);
        $page = $request->input('_page', 1);
        $sort = $request->input('_sort', 'id_contato_ong');
        $order = $request->input('_order', 'asc');
        $filter = json_decode($request->input('filter', '{}'), true);

        $query = ContatoOng::query();

        if (!empty($filter)) {
            foreach ($filter as $field => $value) {
                if ($value) {
                    $query->where($field, 'like', "%{$value}%");
                }
            }
        }

        $query->orderBy($sort, $order);

        $contatos = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json($contatos->items())
            ->header('X-Total-Count', $contatos->total())
            ->header('Access-Control-Expose-Headers', 'X-Total-Count');
    }

    /**
     * Criar novo contato
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_ong' => 'required|exists:ongs,id_ong',
            'tipo_contato' => 'required|in:telefone,email,redesocial',
            'valor_contato' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $contato = ContatoOng::create($request->all());

        return response()->json(['data' => $contato], 201);
    }

    /**
     * Exibir um contato específico
     */
    public function show($id): JsonResponse
    {
        $contato = ContatoOng::find($id);

        if (!$contato) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }

        return response()->json(['data' => $contato]);
    }

    /**
     * Atualizar um contato
     */
    public function update(Request $request, $id): JsonResponse
    {
        $contato = ContatoOng::find($id);

        if (!$contato) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_ong' => 'sometimes|required|exists:ongs,id_ong',
            'tipo_contato' => 'sometimes|required|in:telefone,email,redesocial',
            'valor_contato' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $contato->update($request->all());

        return response()->json(['data' => $contato]);
    }

    /**
     * Deletar um contato (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        $contato = ContatoOng::find($id);

        if (!$contato) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }

        $contato->delete();

        return response()->json(['data' => $contato]);
    }

    /**
     * Restaurar contato deletado
     */
    public function restore($id): JsonResponse
    {
        $contato = ContatoOng::withTrashed()->find($id);

        if (!$contato) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }

        $contato->restore();

        return response()->json(['data' => $contato]);
    }
}
