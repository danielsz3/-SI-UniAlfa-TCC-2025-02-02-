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
     * Listar transações
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
            'tipo'            => 'required|in:receita,despesa',
            'valor'           => 'required|numeric|min:0.01',
            'data'            => 'required|date|before_or_equal:today',
            'categoria'       => 'required|string|min:2|max:100',
            'descricao'       => 'required|string|min:3|max:255',
            'forma_pagamento' => 'required|string',
            'situacao'        => 'required|in:pendente,concluido,cancelado',
            'observacao'      => 'nullable|string|max:1000',
        ], [
            'tipo.in' => 'O tipo deve ser receita ou despesa.',
            'valor.min' => 'O valor deve ser no mínimo 0.01.',
            'data.before_or_equal' => 'A data não pode ser futura.',
            'categoria.min' => 'A categoria deve ter no mínimo 2 caracteres.',
            'categoria.max' => 'A categoria deve ter no máximo 100 caracteres.',
            'descricao.min' => 'A descrição deve ter no mínimo 3 caracteres.',
            'descricao.max' => 'A descrição deve ter no máximo 255 caracteres.',
            'situacao.in' => 'A situação deve ser pendente, concluido ou cancelado.',
            'observacao.max' => 'A observação deve ter no máximo 1000 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $transacao = Transacao::create($request->only([
                'tipo', 'valor', 'data', 'categoria', 'descricao', 'forma_pagamento', 'situacao', 'observacao'
            ]));

            return response()->json($transacao, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Exibir uma transação
     */
    public function show($id): JsonResponse
    {
        $transacao = Transacao::find($id);

        if (!$transacao) {
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }

        return response()->json($transacao, 200);
    }

    /**
     * Atualizar transação
     */
    public function update(Request $request, $id): JsonResponse
    {
        $transacao = Transacao::find($id);

        if (!$transacao) {
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'tipo'            => 'sometimes|required|in:receita,despesa',
            'valor'           => 'sometimes|required|numeric|min:0.01',
            'data'            => 'sometimes|required|date|before_or_equal:today',
            'categoria'       => 'sometimes|required|string|min:2|max:100',
            'descricao'       => 'sometimes|required|string|min:3|max:255',
            'forma_pagamento' => 'sometimes|required|string',
            'situacao'        => 'sometimes|required|in:pendente,concluido,cancelado',
            'observacao'      => 'nullable|string|max:1000',
        ], [
            'tipo.in' => 'O tipo deve ser receita ou despesa.',
            'valor.min' => 'O valor deve ser no mínimo 0.01.',
            'data.before_or_equal' => 'A data não pode ser futura.',
            'categoria.min' => 'A categoria deve ter no mínimo 2 caracteres.',
            'categoria.max' => 'A categoria deve ter no máximo 100 caracteres.',
            'descricao.min' => 'A descrição deve ter no mínimo 3 caracteres.',
            'descricao.max' => 'A descrição deve ter no máximo 255 caracteres.',
            'situacao.in' => 'A situação deve ser pendente, concluido ou cancelado.',
            'observacao.max' => 'A observação deve ter no máximo 1000 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transacao->update($request->only([
            'tipo', 'valor', 'data', 'categoria', 'descricao', 'forma_pagamento', 'situacao', 'observacao'
        ]));

        return response()->json($transacao->fresh(), 200);
    }

    /**
     * Deletar transação (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        $transacao = Transacao::find($id);

        if (!$transacao) {
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }

        $transacao->delete();

        return response()->json(null, 204);
    }

    /**
     * Restaurar transação deletada
     */
    public function restore($id): JsonResponse
    {
        $transacao = Transacao::withTrashed()->find($id);

        if (!$transacao) {
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }

        if (!$transacao->trashed()) {
            return response()->json(['error' => 'Transação já está ativa'], 400);
        }

        $transacao->restore();

        return response()->json($transacao, 200);
    }
}