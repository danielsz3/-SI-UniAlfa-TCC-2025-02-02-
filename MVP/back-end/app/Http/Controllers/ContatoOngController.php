<?php

namespace App\Http\Controllers;

use App\Models\ContatoOng;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        $order = strtolower($request->input('_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $filter = json_decode($request->input('filter', '{}'), true) ?? [];

        $query = ContatoOng::query();

        // filtros básicos
        foreach ($filter as $field => $value) {
            if ($value) {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        $query->orderBy($sort, $order);

        $contatos = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $contatos->getCollection()->map(fn($c) => $this->formatContato($c));

        return response()->json($data)
            ->header('X-Total-Count', $contatos->total())
            ->header('Access-Control-Expose-Headers', 'X-Total-Count');
    }

    /**
     * Criar novo contato
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_ong'        => 'required|exists:ongs,id_ong',
            'tipo_contato'  => 'required|in:telefone,email,redesocial',
            'valor_contato' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contato = ContatoOng::create($request->all());

        return response()->json(['data' => $this->formatContato($contato)], 201);
    }

    /**
     * Exibir um contato específico
     */
    public function show($id): JsonResponse
    {
        try {
            $contato = ContatoOng::withTrashed()->findOrFail($id);
            return response()->json(['data' => $this->formatContato($contato)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }
    }

    /**
     * Atualizar um contato
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $contato = ContatoOng::withTrashed()->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'id_ong'        => 'sometimes|required|exists:ongs,id_ong',
                'tipo_contato'  => 'sometimes|required|in:telefone,email,redesocial',
                'valor_contato' => 'sometimes|required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $contato->update($request->all());

            return response()->json(['data' => $this->formatContato($contato)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }
    }

    /**
     * Deletar um contato (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $contato = ContatoOng::findOrFail($id);
            $contato->delete();

            return response()->json(['message' => 'Contato inativado com sucesso']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }
    }

    /**
     * Restaurar contato deletado
     */
    public function restore($id): JsonResponse
    {
        try {
            $contato = ContatoOng::withTrashed()->findOrFail($id);
            $contato->restore();

            return response()->json(['message' => 'Contato restaurado com sucesso']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }
    }

    /**
     * Alternar status (ativo/inativo)
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $contato = ContatoOng::withTrashed()->findOrFail($id);

            if ($contato->trashed()) {
                $contato->restore();
                $msg = 'Contato ativado com sucesso';
            } else {
                $contato->delete();
                $msg = 'Contato inativado com sucesso';
            }

            return response()->json(['message' => $msg, 'data' => $this->formatContato($contato)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Contato não encontrado'], 404);
        }
    }

    /**
     * Formatador de saída
     */
    private function formatContato($contato): array
    {
        return [
            'id_contato_ong' => $contato->id_contato_ong,
            'id_ong'         => $contato->id_ong,
            'tipo_contato'   => $contato->tipo_contato,
            'valor_contato'  => $contato->valor_contato,
            'status'         => $contato->deleted_at ? 'inativo' : 'ativo',
            'created_at'     => $contato->created_at,
            'updated_at'     => $contato->updated_at,
            'deleted_at'     => $contato->deleted_at,
        ];
    }
}