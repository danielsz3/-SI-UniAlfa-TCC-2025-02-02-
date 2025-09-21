<?php

namespace App\Http\Controllers;

use App\Models\Parceiro;
use App\Traits\SearchIndex;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ParceiroController extends Controller
{
    use SearchIndex;

    /**
     * Listar parceiros (com paginação + filtros dinâmicos)
     */
    public function index(Request $request): JsonResponse
    {
        return $this->SearchIndex(
            $request,
            Parceiro::query(),
            'parceiros',
            ['nome', 'url_site', 'url_logo', 'descricao']
        );
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
            'nome' => 'required|string|max:255',
            'url_site'      => 'nullable|url',
            'url_logo'      => 'nullable|url',
            'descricao'     => 'nullable|string|max:500',
        ], [
            'nome.required' => 'O nome do parceiro é obrigatório',
            'nome.max'      => 'O nome do parceiro não pode ter mais de 255 caracteres',
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
                'nome',
                'url_site',
                'url_logo',
                'descricao'
            ]));

            return response()->json($parceiro,201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => $e->getMessage()
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

        return response()->json($parceiro,200);
    }


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

            return response()->json($parceiro->fresh(), 200);
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
