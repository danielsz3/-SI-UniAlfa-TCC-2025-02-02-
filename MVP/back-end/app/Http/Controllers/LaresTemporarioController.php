<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LarTemporario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class LaresTemporarioController extends Controller
{
    /**
     * Listar lares temporários com paginação, ordenação e filtros dinâmicos
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

        $query = LarTemporario::query();

        // 🚀 aplica filtros vindos pela URL
        foreach ($request->query() as $field => $value) {
            if (in_array($field, ['_start','_end','_sort','_order','_page','_limit'])) {
                continue;
            }
            if ($value === null || $value === '') continue;

            // Range ex: ?data_nascimento_from=1980-01-01&data_nascimento_to=2000-12-31
            if (preg_match('/(.+)_from$/', $field, $matches)) {
                $query->where($matches[1], '>=', $value);
                continue;
            }
            if (preg_match('/(.+)_to$/', $field, $matches)) {
                $query->where($matches[1], '<=', $value);
                continue;
            }

            // LIKE em campos textuais
            if (in_array($field, ['telefone', 'experiencia'])) {
                $query->where($field, 'like', "%{$value}%");
            } else {
                $query->where($field, $value);
            }
        }

        // ordenação
        $query->orderBy($sort, $order);

        // paginação
        $lares = $query->paginate($perPage, ['*'], 'page', $page);

        return response()
            ->json($lares->items())
            ->header('X-Total-Count', $lares->total())
            ->header('Access-Control-Expose-Headers', 'X-Total-Count');
    }

    /**
     * Criar novo lar temporário
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data_nascimento' => 'required|date|before:today',
            'telefone'        => 'required|string|max:20',
            'situacao'        => 'required|in:ativo,inativo',
            'experiencia'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $lar = LarTemporario::create($request->all());

        return response()->json($lar, 201);
    }

    /**
     * Exibir um lar temporário
     */
    public function show($id): JsonResponse
    {
        return response()->json(LarTemporario::findOrFail($id), 200);
    }

    /**
     * Atualizar lar temporário
     */
    public function update(Request $request, $id): JsonResponse
    {
        $lar = LarTemporario::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'data_nascimento' => 'sometimes|required|date|before:today',
            'telefone'        => 'sometimes|required|string|max:20',
            'situacao'        => 'sometimes|required|in:ativo,inativo',
            'experiencia'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $lar->update($request->all());

        return response()->json($lar, 200);
    }

    /**
     * Deletar lar temporário (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        $lar = LarTemporario::findOrFail($id);
        $lar->delete();

        return response()->json(['message' => 'Lar temporário deletado com sucesso'], 200);
    }
}