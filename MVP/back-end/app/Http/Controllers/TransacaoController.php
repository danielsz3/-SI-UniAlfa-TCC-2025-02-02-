<?php

namespace App\Http\Controllers;

use App\Models\Transacao;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TransacaoController extends Controller
{
    /**
     * Listar transações com paginação, ordenação e filtros dinâmicos
     */
    public function index(Request $request): JsonResponse
    {
        // Paginação (suporte aos dois formatos: json-server e simple-rest)
        $start   = (int) $request->query('_start', 0);
        $end     = (int) $request->query('_end', 0);
        $perPage = (int) $request->input('_limit', ($end > 0 ? ($end - $start) : 10));
        $page    = (int) $request->input('_page', ($perPage > 0 ? intval($start / $perPage) + 1 : 1));

        // Ordenação
        $sort  = $request->query('_sort', 'id');
        $order = $request->query('_order', 'ASC');

        $query = Transacao::query();

        // 🚀 aplica filtros vindos pela URL
        foreach ($request->query() as $field => $value) {
            if (in_array($field, ['_start','_end','_sort','_order','_page','_limit'])) {
                continue; // ignora params de paginação e ordenação
            }
            if ($value === null || $value === '') continue;

            // Range ex: ?data_transacao_from=2024-01-01&data_transacao_to=2024-12-31
            if (preg_match('/(.+)_from$/', $field, $matches)) {
                $query->where($matches[1], '>=', $value);
                continue;
            }
            if (preg_match('/(.+)_to$/', $field, $matches)) {
                $query->where($matches[1], '<=', $value);
                continue;
            }

            // LIKE em campos textuais
            if (in_array($field, ['descricao','categoria'])) {
                $query->where($field, 'like', "%{$value}%");
            } else {
                $query->where($field, $value);
            }
        }

        // ordenação
        $query->orderBy($sort, $order);

        // paginação
        $transacoes = $query->paginate($perPage, ['*'], 'page', $page);

        return response()
            ->json($transacoes->items())
            ->header('X-Total-Count', $transacoes->total())
            ->header('Access-Control-Expose-Headers', 'X-Total-Count');
    }

    /**
     * Criar nova transação
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'valor'          => 'required|numeric',
            'descricao'      => 'required|string|max:255',
            'categoria'      => 'required|string|max:255',
            'tipo_transacao' => 'required|in:entrada,saida',
            'data_transacao' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $transacao = Transacao::create($request->all());

        return response()->json($transacao, 201);
    }

    /**
     * Exibir uma transação
     */
    public function show($id): JsonResponse
    {
        return response()->json(Transacao::findOrFail($id), 200);
    }

    /**
     * Atualizar transação
     */
    public function update(Request $request, $id): JsonResponse
    {
        $transacao = Transacao::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'valor'          => 'sometimes|required|numeric',
            'descricao'      => 'sometimes|required|string|max:255',
            'categoria'      => 'sometimes|required|string|max:255',
            'tipo_transacao' => 'sometimes|required|in:entrada,saida',
            'data_transacao' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $transacao->update($request->all());

        return response()->json($transacao, 200);
    }

    /**
     * Deletar transação (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        $transacao = Transacao::findOrFail($id);
        $transacao->delete();

        return response()->json(['message' => 'Transação deletada com sucesso'], 200);
    }
}