<?php

namespace App\Http\Controllers;

use App\Models\ContatoOng;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ContatoOngController extends Controller
{
    /**
     * Listar contatos com paginação, ordenação e filtros dinâmicos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('_limit', 10);
            $page    = $request->input('_page', 1);
            $sort    = $request->input('_sort', 'id');
            $order   = $request->input('_order', 'asc');
            $filter  = json_decode($request->input('filter', '{}'), true);

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

            return response()
                ->json($contatos->items())
                ->header('X-Total-Count', $contatos->total())
                ->header('Access-Control-Expose-Headers', 'X-Total-Count');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar os contatos'
            ], 500);
        }
    }

    /**
     * Criar novo contato
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_ong'        => 'required|exists:ongs,id',
            'tipo_contato'  => 'required|in:telefone,email,redesocial',
            'valor_contato' => 'required|string|max:255',
        ], [
            'id_ong.required' => 'A ONG vinculada é obrigatória',
            'id_ong.exists' => 'A ONG informada não existe',
            'tipo_contato.required' => 'O tipo de contato é obrigatório',
            'tipo_contato.in' => 'O tipo deve ser telefone, email ou redesocial',
            'valor_contato.required' => 'O valor do contato é obrigatório',
            'valor_contato.max' => 'O contato não pode ter mais de 255 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $contato = ContatoOng::create($request->only(['id_ong', 'tipo_contato', 'valor_contato']));

            return response()->json([
                'message' => 'Contato criado com sucesso!',
                'data' => $contato
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível criar o contato'
            ], 500);
        }
    }

    /**
     * Exibir um contato específico
     */
    public function show($id): JsonResponse
    {
        try {
            $contato = ContatoOng::find($id);

            if (!$contato) {
                return response()->json([
                    'error' => 'Contato não encontrado'
                ], 404);
            }

            return response()->json([
                'message' => 'Contato encontrado',
                'data' => $contato
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar o contato'
            ], 500);
        }
    }

    /**
     * Atualizar um contato
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $contato = ContatoOng::find($id);

            if (!$contato) {
                return response()->json([
                    'error' => 'Contato não encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'id_ong'        => 'sometimes|required|exists:ongs,id',
                'tipo_contato'  => 'sometimes|required|in:telefone,email,redesocial',
                'valor_contato' => 'sometimes|required|string|max:255',
            ], [
                'id_ong.required' => 'A ONG vinculada é obrigatória',
                'id_ong.exists' => 'A ONG informada não existe',
                'tipo_contato.required' => 'O tipo de contato é obrigatório',
                'tipo_contato.in' => 'O tipo deve ser telefone, email ou redesocial',
                'valor_contato.required' => 'O valor do contato é obrigatório',
                'valor_contato.max' => 'O contato não pode ter mais de 255 caracteres',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $contato->update($request->only(['id_ong', 'tipo_contato', 'valor_contato']));

            return response()->json([
                'message' => 'Contato atualizado com sucesso!',
                'data' => $contato->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível atualizar o contato'
            ], 500);
        }
    }

    /**
     * Deletar um contato (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $contato = ContatoOng::find($id);

            if (!$contato) {
                return response()->json([
                    'error' => 'Contato não encontrado'
                ], 404);
            }

            $contato->delete();

            return response()->json([
                'message' => 'Contato excluído com sucesso!',
                'data' => $contato
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível excluir o contato'
            ], 500);
        }
    }

    /**
     * Restaurar contato deletado
     */
    public function restore($id): JsonResponse
    {
        try {
            $contato = ContatoOng::withTrashed()->find($id);

            if (!$contato) {
                return response()->json([
                    'error' => 'Contato não encontrado'
                ], 404);
            }

            if (!$contato->trashed()) {
                return response()->json([
                    'error' => 'Contato já está ativo'
                ], 400);
            }

            $contato->restore();

            return response()->json([
                'message' => 'Contato restaurado com sucesso!',
                'data' => $contato
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível restaurar o contato'
            ], 500);
        }
    }
}