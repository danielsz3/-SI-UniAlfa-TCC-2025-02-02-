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
        try {
            $start   = (int) $request->query('_start', 0);
            $end     = (int) $request->query('_end', 0);
            $perPage = (int) $request->input('_limit', ($end > 0 ? ($end - $start) : 10));
            $page    = (int) $request->input('_page', ($perPage > 0 ? intval($start / $perPage) + 1 : 1));

            $sort  = $request->query('_sort', 'id');
            $order = $request->query('_order', 'ASC');

            $query = LarTemporario::query();

            foreach ($request->query() as $field => $value) {
                if (in_array($field, ['_start','_end','_sort','_order','_page','_limit'])) continue;
                if ($value === null || $value === '') continue;

                if (preg_match('/(.+)_from$/', $field, $matches)) {
                    $query->where($matches[1], '>=', $value);
                    continue;
                }
                if (preg_match('/(.+)_to$/', $field, $matches)) {
                    $query->where($matches[1], '<=', $value);
                    continue;
                }

                if (in_array($field, ['nome','telefone','experiencia'])) {
                    $query->where($field, 'like', "%{$value}%");
                } else {
                    $query->where($field, $value);
                }
            }

            $query->orderBy($sort, $order);

            $lares = $query->paginate($perPage, ['*'], 'page', $page);

            return response()
                ->json($lares->items())
                ->header('X-Total-Count', $lares->total())
                ->header('Access-Control-Expose-Headers', 'X-Total-Count');

        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível carregar os lares temporários'], 500);
        }
    }

    /**
     * Criar lar temporário
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome'            => 'required|string|min:2|max:150',
            'data_nascimento' => 'required|date|before:today|after:1900-01-01',
            'telefone'        => 'required|string|size:11|regex:/^[0-9]+$/',
            'situacao'        => 'required|in:ativo,inativo',
            'experiencia'     => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $lar = LarTemporario::create($request->only([
                'nome','data_nascimento','telefone','situacao','experiencia'
            ]));

            return response()->json($lar, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível criar o lar temporário'], 500);
        }
    }

    /**
     * Exibir lar temporário
     */
    public function show($id): JsonResponse
    {
        try {
            $lar = LarTemporario::with('enderecos')->find($id);

            if (!$lar) {
                return response()->json(['error' => 'Lar temporário não encontrado'], 404);
            }

            return response()->json($lar, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível carregar o lar temporário'], 500);
        }
    }

    /**
     * Atualizar lar temporário
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $lar = LarTemporario::find($id);

            if (!$lar) {
                return response()->json(['error' => 'Lar temporário não encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'nome'            => 'sometimes|required|string|min:2|max:150',
                'data_nascimento' => 'sometimes|required|date|before:today|after:1900-01-01',
                'telefone'        => 'sometimes|required|string|size:11|regex:/^[0-9]+$/',
                'situacao'        => 'sometimes|required|in:ativo,inativo',
                'experiencia'     => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $lar->update($request->only([
                'nome','data_nascimento','telefone','situacao','experiencia'
            ]));

            return response()->json($lar->fresh(), 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível atualizar o lar temporário'], 500);
        }
    }

    /**
     * Deletar lar temporário
     */
    public function destroy($id): JsonResponse
    {
        try {
            $lar = LarTemporario::find($id);

            if (!$lar) {
                return response()->json(['error' => 'Lar temporário não encontrado'], 404);
            }

            $lar->delete();

            return response()->json(null, 204);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível excluir o lar temporário'], 500);
        }
    }

    /**
     * Restaurar lar temporário
     */
    public function restore($id): JsonResponse
    {
        try {
            $lar = LarTemporario::withTrashed()->find($id);

            if (!$lar) {
                return response()->json(['error' => 'Lar temporário não encontrado'], 404);
            }

            if (!$lar->trashed()) {
                return response()->json(['error' => 'Lar temporário já está ativo'], 400);
            }

            $lar->restore();

            return response()->json($lar, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível restaurar o lar temporário'], 500);
        }
    }
}