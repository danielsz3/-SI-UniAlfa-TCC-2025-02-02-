<?php

namespace App\Http\Controllers;

use App\Models\Parceiro;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ParceiroController extends Controller
{
    /**
     * Listar parceiros (com paginação + filtros dinâmicos)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('_limit', 10);
            $page    = (int) $request->input('_page', 1);

            $sort  = $request->input('_sort', 'id');
            $order = $request->input('_order', 'ASC');

            $filter = json_decode($request->input('filter', '{}'), true);

            $query = Parceiro::query();

            if (!empty($filter)) {
                foreach ($filter as $field => $value) {
                    if ($value === null || $value === '') continue;

                    // Suporte a ranges
                    if (is_array($value) && isset($value['from'])) {
                        $query->where($field, '>=', $value['from']);
                        if (isset($value['to'])) {
                            $query->where($field, '<=', $value['to']);
                        }
                        continue;
                    }

                    // Suporte a lista separada por vírgula
                    if (is_string($value) && str_contains($value, ',')) {
                        $query->whereIn($field, explode(',', $value));
                        continue;
                    }

                    // Campos textuais
                    if (in_array($field, ['nome_parceiro', 'email', 'telefone'])) {
                        $query->where($field, 'like', "%{$value}%");
                    } else {
                        $query->where($field, $value);
                    }
                }
            }

            $query->orderBy($sort, $order);

            $parceiros = $query->paginate($perPage, ['*'], 'page', $page);

            return response()
                ->json($parceiros->items())
                ->header('X-Total-Count', $parceiros->total())
                ->header('Access-Control-Expose-Headers', 'X-Total-Count');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => 'Não foi possível carregar os parceiros'
            ], 500);
        }
    }

    /**
     * Listar parceiros incluindo deletados (soft delete)
     */
    public function indexWithTrashed(): JsonResponse
    {
        $parceiros = Parceiro::withTrashed()->get();

        return response()->json([
            'message' => 'Lista de parceiros (incluindo deletados)',
            'data' => $parceiros,
            'total' => $parceiros->count()
        ]);
    }

    /**
     * Criar parceiro
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome_parceiro' => 'required|string|max:255',
            'url_site'      => 'nullable|url',
            'url_logo'      => 'nullable|url',
            'descricao'     => 'nullable|string|max:500',
        ], [
            'nome_parceiro.required' => 'O nome do parceiro é obrigatório',
            'nome_parceiro.max'      => 'O nome do parceiro não pode ter mais de 255 caracteres',
            'url_site.url'           => 'Digite uma URL válida para o site',
            'url_logo.url'           => 'Digite uma URL válida para a logo',
            'descricao.max'          => 'A descrição não pode ter mais de 500 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $parceiro = Parceiro::create($request->only([
                'nome_parceiro',
                'url_site',
                'url_logo',
                'descricao'
            ]));

            return response()->json([
                'message' => 'Parceiro criado com sucesso!',
                'data' => $parceiro
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => 'Não foi possível criar o parceiro'
            ], 500);
        }
    }

    /**
     * Exibir parceiro
     */
    public function show($id): JsonResponse
    {
        $parceiro = Parceiro::find($id);

        if (!$parceiro) {
            return response()->json([
                'error' => 'Parceiro não encontrado'
            ], 404);
        }

        return response()->json([
            'message' => 'Parceiro encontrado',
            'data' => $parceiro
        ]);
    }

    /**
     * Atualizar parceiro
     */
    public function update(Request $request, $id): JsonResponse
    {
        $parceiro = Parceiro::find($id);

        if (!$parceiro) {
            return response()->json(['error' => 'Parceiro não encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nome_parceiro' => 'sometimes|required|string|max:255',
            'url_site'      => 'nullable|url',
            'url_logo'      => 'nullable|url',
            'descricao'     => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $parceiro->update($request->only([
                'nome_parceiro',
                'url_site',
                'url_logo',
                'descricao'
            ]));

            return response()->json([
                'message' => 'Parceiro atualizado com sucesso!',
                'data' => $parceiro->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => 'Não foi possível atualizar o parceiro'
            ], 500);
        }
    }

    /**
     * Deletar parceiro (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        $parceiro = Parceiro::find($id);

        if (!$parceiro) {
            return response()->json(['error' => 'Parceiro não encontrado'], 404);
        }

        try {
            $parceiro->delete();

            return response()->json([
                'message' => 'Parceiro excluído com sucesso!',
                'data' => $parceiro
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => 'Não foi possível excluir o parceiro'
            ], 500);
        }
    }

    /**
     * Restaurar parceiro deletado
     */
    public function restore($id): JsonResponse
    {
        $parceiro = Parceiro::withTrashed()->find($id);

        if (!$parceiro) {
            return response()->json(['error' => 'Parceiro não encontrado'], 404);
        }

        try {
            if (!$parceiro->trashed()) {
                return response()->json([
                    'error' => 'O parceiro já está ativo'
                ], 400);
            }

            $parceiro->restore();

            return response()->json([
                'message' => 'Parceiro restaurado com sucesso!',
                'data' => $parceiro
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => 'Não foi possível restaurar o parceiro'
            ], 500);
        }
    }
}
