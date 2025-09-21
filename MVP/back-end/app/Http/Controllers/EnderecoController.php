<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EnderecoController extends Controller
{
    /**
     * Lista todos os endereços
     */
    public function index(): JsonResponse
    {
        $enderecos = Endereco::with('usuario')->get();
        return response()->json($enderecos, 200);
    }

    /**
     * Cadastra um novo endereço
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'cep'         => 'required|string|max:9',
            'logradouro'  => 'required|string|max:255',
            'numero'      => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:100',
            'bairro'      => 'required|string|max:100',
            'cidade'      => 'required|string|max:100',
            'uf'          => 'required|string|max:2',
            'id_usuario'  => 'required|exists:usuarios,id_usuario',
        ]);

        $endereco = Endereco::create($request->all());

        return response()->json($endereco, 201);
    }

    /**
     * Exibe um endereço específico
     */
    public function show($id): JsonResponse
    {
        $endereco = Endereco::with('usuario')->find($id);

        if (!$endereco) {
            return response()->json(['error' => 'Endereço não encontrado'], 404);
        }

        return response()->json($endereco, 200);
    }

    /**
     * Atualiza um endereço
     */
    public function update(Request $request, $id): JsonResponse
    {
        $endereco = Endereco::find($id);

        if (!$endereco) {
            return response()->json(['error' => 'Endereço não encontrado'], 404);
        }

        $request->validate([
            'cep'         => 'sometimes|required|string|max:9',
            'logradouro'  => 'sometimes|required|string|max:255',
            'numero'      => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:100',
            'bairro'      => 'sometimes|required|string|max:100',
            'cidade'      => 'sometimes|required|string|max:100',
            'uf'          => 'sometimes|required|string|max:2',
            'id_usuario'  => 'sometimes|required|exists:usuarios,id_usuario',
        ]);

        $endereco->update($request->all());

        return response()->json($endereco->fresh(), 200);
    }

    /**
     * Remove um endereço (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        $endereco = Endereco::find($id);

        if (!$endereco) {
            return response()->json(['error' => 'Endereço não encontrado'], 404);
        }

        $endereco->delete();

        return response()->json(null, 204);
    }

    /**
     * Restaura um endereço soft deleted
     */
    public function restore($id): JsonResponse
    {
        $endereco = Endereco::withTrashed()->find($id);

        if (!$endereco) {
            return response()->json(['error' => 'Endereço não encontrado'], 404);
        }

        if (!$endereco->trashed()) {
            return response()->json(['error' => 'Este endereço já está ativo'], 400);
        }

        $endereco->restore();

        return response()->json($endereco, 200);
    }
}