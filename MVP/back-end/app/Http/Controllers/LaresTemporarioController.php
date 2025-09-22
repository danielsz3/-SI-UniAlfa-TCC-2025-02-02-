<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LarTemporario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Traits\SearchIndex;

class LaresTemporarioController extends Controller
{
    use SearchIndex;
    /**
     * Listar lares temporários com paginação, ordenação e filtros dinâmicos
     */
    public function index(Request $request): JsonResponse
    {
        return $this->SearchIndex(
            $request,
            LarTemporario::query(),
            'lares_temporarios',
            ['nome', 'data_nascimento', 'telefone']
        );
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
            return response()->json(['error' => $e->getMessage()], 500);
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