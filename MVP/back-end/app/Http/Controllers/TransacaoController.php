<?php

namespace App\Http\Controllers;

use App\Models\Transacao;
use App\Traits\SearchIndex;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TransacaoController extends Controller
{
    use SearchIndex;

    /**
     * Listar transações (getList)
     */
    public function index(Request $request): JsonResponse
    {
        return $this->searchIndex(
            $request,
            Transacao::query(),
            'transacoes',
            ['descricao', 'categoria']
        );
    }

    /**
     * Criar nova transação
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'valor'          => 'required|numeric|min:0.01',
            'descricao'      => 'required|string|min:3|max:255',
            'categoria'      => 'required|string|min:2|max:100',
            'tipo_transacao' => 'required|in:entrada,saida',
            'data_transacao' => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $transacao = Transacao::create($request->only([
                'valor', 'descricao', 'categoria', 'tipo_transacao', 'data_transacao'
            ]));

            return response()->json($transacao, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível criar a transação'], 500);
        }
    }

    /**
     * Exibir uma transação (getOne)
     */
    public function show($id): JsonResponse
    {
        try {
            $transacao = Transacao::find($id);

            if (!$transacao) {
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            return response()->json($transacao, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível carregar a transação'], 500);
        }
    }

    /**
     * Atualizar transação
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $transacao = Transacao::find($id);

            if (!$transacao) {
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            $validator = Validator::make($request->all(), [
                'valor'          => 'sometimes|required|numeric|min:0.01',
                'descricao'      => 'sometimes|required|string|min:3|max:255',
                'categoria'      => 'sometimes|required|string|min:2|max:100',
                'tipo_transacao' => 'sometimes|required|in:entrada,saida',
                'data_transacao' => 'sometimes|required|date|before_or_equal:today',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $transacao->update($request->only([
                'valor', 'descricao', 'categoria', 'tipo_transacao', 'data_transacao'
            ]));

            return response()->json($transacao->fresh(), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível atualizar a transação'], 500);
        }
    }

    /**
     * Deletar transação (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $transacao = Transacao::find($id);

            if (!$transacao) {
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            $transacao->delete();

            return response()->json(null, 204); // ✅ só status code
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível excluir a transação'], 500);
        }
    }

    /**
     * Restaurar transação deletada
     */
    public function restore($id): JsonResponse
    {
        try {
            $transacao = Transacao::withTrashed()->find($id);

            if (!$transacao) {
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            if (!$transacao->trashed()) {
                return response()->json(['error' => 'Transação já está ativa'], 400);
            }

            $transacao->restore();

            return response()->json($transacao, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível restaurar a transação'], 500);
        }
    }
}