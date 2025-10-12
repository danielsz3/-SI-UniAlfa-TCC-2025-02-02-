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

    public function index(Request $request): JsonResponse
    {
        return $this->SearchIndex(
            $request,
            Transacao::query(),
            'transacoes',
            ['descricao', 'categoria']
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tipo'            => 'required|in:entrada,saida',
            'valor'           => 'required|numeric|min:0.01',
            'data'            => 'required|date|after:1900-01-01|before_or_equal:today',
            'categoria'       => 'required|string|min:2|max:100',
            'descricao'       => 'required|string|min:3|max:255',
            'forma_pagamento' => 'required|string|max:255',
            'situacao'        => 'required|in:pendente,concluido,cancelado',
            'observacao'      => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $payload = $request->only([
                'tipo','valor','data','categoria','descricao','forma_pagamento','situacao','observacao'
            ]);

            $transacao = Transacao::create($payload);

            return response()->json($transacao, 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Não foi possível criar a transação',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $transacao = Transacao::find($id);
        if (!$transacao) {
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }
        return response()->json($transacao, 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $transacao = Transacao::find($id);
        if (!$transacao) {
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'tipo'            => 'sometimes|required|in:entrada,saida',
            'valor'           => 'sometimes|required|numeric|min:0.01',
            'data'            => 'sometimes|required|date|after:1900-01-01|before_or_equal:today',
            'categoria'       => 'sometimes|required|string|min:2|max:100',
            'descricao'       => 'sometimes|required|string|min:3|max:255',
            'forma_pagamento' => 'sometimes|required|string|max:255',
            'situacao'        => 'sometimes|required|in:pendente,concluido,cancelado',
            'observacao'      => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $transacao->update($request->only([
                'tipo','valor','data','categoria','descricao','forma_pagamento','situacao','observacao'
            ]));

            return response()->json($transacao->fresh(), 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Não foi possível atualizar a transação',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $transacao = Transacao::find($id);
        if (!$transacao) {
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }

        $transacao->delete();
        return response()->json(null, 204);
    }

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
        return response()->json($transacao->fresh(), 200);
    }
}