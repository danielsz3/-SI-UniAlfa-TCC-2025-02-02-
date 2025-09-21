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
     * Listar transações com paginação, ordenação e filtros dinâmicos
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
        ], [
            // Mensagens personalizadas
            'valor.required' => 'O valor é obrigatório',
            'valor.numeric' => 'O valor deve ser um número',
            'valor.min' => 'O valor deve ser maior que zero',

            'descricao.required' => 'A descrição é obrigatória',
            'descricao.min' => 'A descrição deve ter pelo menos 3 caracteres',
            'descricao.max' => 'A descrição não pode ter mais de 255 caracteres',

            'categoria.required' => 'A categoria é obrigatória',
            'categoria.min' => 'A categoria deve ter pelo menos 2 caracteres',
            'categoria.max' => 'A categoria não pode ter mais de 100 caracteres',

            'tipo_transacao.required' => 'O tipo de transação é obrigatório',
            'tipo_transacao.in' => 'O tipo deve ser "entrada" ou "saida"',

            'data_transacao.required' => 'A data da transação é obrigatória',
            'data_transacao.date' => 'Digite uma data válida',
            'data_transacao.before_or_equal' => 'A data não pode ser futura',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Dados inválidos',
                'message' => 'Verifique os campos e tente novamente',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transacao = Transacao::create($request->only([
                'valor', 'descricao', 'categoria', 'tipo_transacao', 'data_transacao'
            ]));

            return response()->json($transacao, 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível criar a transação'
            ], 500);
        }
    }

    /**
     * Exibir uma transação específica
     */
    public function show($id): JsonResponse
    {
        try {
            $transacao = Transacao::find($id);

            if (!$transacao) {
                return response()->json([
                    'error' => 'Transação não encontrada',
                    'message' => 'A transação solicitada não existe'
                ], 404);
            }

            return response()->json($transacao, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar a transação'
            ], 500);
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
                return response()->json([
                    'error' => 'Transação não encontrada',
                    'message' => 'A transação que você está tentando atualizar não existe'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'valor'          => 'sometimes|required|numeric|min:0.01',
                'descricao'      => 'sometimes|required|string|min:3|max:255',
                'categoria'      => 'sometimes|required|string|min:2|max:100',
                'tipo_transacao' => 'sometimes|required|in:entrada,saida',
                'data_transacao' => 'sometimes|required|date|before_or_equal:today',
            ], [
                // Mensagens personalizadas (mesmas do store)
                'valor.required' => 'O valor é obrigatório',
                'valor.numeric' => 'O valor deve ser um número',
                'valor.min' => 'O valor deve ser maior que zero',

                'descricao.required' => 'A descrição é obrigatória',
                'descricao.min' => 'A descrição deve ter pelo menos 3 caracteres',
                'descricao.max' => 'A descrição não pode ter mais de 255 caracteres',

                'categoria.required' => 'A categoria é obrigatória',
                'categoria.min' => 'A categoria deve ter pelo menos 2 caracteres',
                'categoria.max' => 'A categoria não pode ter mais de 100 caracteres',

                'tipo_transacao.required' => 'O tipo de transação é obrigatório',
                'tipo_transacao.in' => 'O tipo deve ser "entrada" ou "saida"',

                'data_transacao.required' => 'A data da transação é obrigatória',
                'data_transacao.date' => 'Digite uma data válida',
                'data_transacao.before_or_equal' => 'A data não pode ser futura',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dados inválidos',
                    'message' => 'Verifique os campos e tente novamente',
                    'errors' => $validator->errors()
                ], 422);
            }

            $transacao->update($request->only([
                'valor', 'descricao', 'categoria', 'tipo_transacao', 'data_transacao'
            ]));

            return response()->json($transacao->fresh(), 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível atualizar a transação'
            ], 500);
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
                return response()->json([
                    'error' => 'Transação não encontrada',
                    'message' => 'A transação que você está tentando excluir não existe'
                ], 404);
            }

            $transacao->delete();

            return response()->json($transacao, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível excluir a transação'
            ], 500);
        }
    }

    /**
     * Restaurar transação deletada (se usar SoftDeletes)
     */
    public function restore($id): JsonResponse
    {
        try {
            $transacao = Transacao::withTrashed()->find($id);

            if (!$transacao) {
                return response()->json([
                    'error' => 'Transação não encontrada',
                    'message' => 'A transação que você está tentando restaurar não existe'
                ], 404);
            }

            if (!$transacao->trashed()) {
                return response()->json([
                    'error' => 'Transação já está ativa',
                    'message' => 'Esta transação não precisa ser restaurada'
                ], 400);
            }

            $transacao->restore();

            return response()->json([
                'message' => 'Transação restaurada com sucesso!',
                'data' => $transacao
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível restaurar a transação'
            ], 500);
        }
    }

}
