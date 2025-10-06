<?php

namespace App\Http\Controllers;

use App\Models\Parceiro;
use App\Traits\SearchIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

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
            ['nome', 'url_site', 'descricao']
        );
    }

    /**
     * Criar parceiro
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome'      => 'required|string|max:255',
            'url_site'  => 'nullable|url',
            'descricao' => 'nullable|string|max:500',
            'url_logo'  => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ], [
            'nome.required' => 'O nome do parceiro é obrigatório.',
            'nome.max' => 'O nome do parceiro deve ter no máximo 255 caracteres.',
            'url_site.url' => 'A URL do site deve ser válida.',
            'descricao.max' => 'A descrição deve ter no máximo 500 caracteres.',
            'url_logo.mimes' => 'A logo deve ser uma imagem do tipo jpg, jpeg, png, webp ou gif.',
            'url_logo.max' => 'A logo deve ter no máximo 4MB.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = [
                'nome'      => $request->nome,
                'url_site'  => $request->url_site,
                'descricao' => $request->descricao,
            ];

            if ($request->hasFile('url_logo')) {
                $path = $request->file('url_logo')->store('parceiros', 'public');
                $data['url_logo'] = $path; // salva o caminho do arquivo
            }

            $parceiro = Parceiro::create($data);

            return response()->json($parceiro, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível criar o parceiro'], 500);
        }
    }

    /**
     * Exibir parceiro
     */
    public function show($id): JsonResponse
    {
        try {
            $parceiro = Parceiro::find($id);

            if (!$parceiro) {
                return response()->json(['error' => 'Parceiro não encontrado'], 404);
            }

            return response()->json($parceiro, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível carregar o parceiro'], 500);
        }
    }

    /**
     * Atualizar parceiro
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $parceiro = Parceiro::find($id);

            if (!$parceiro) {
                return response()->json(['error' => 'Parceiro não encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'nome'      => 'sometimes|required|string|max:255',
                'url_site'  => 'nullable|url',
                'descricao' => 'nullable|string|max:500',
                'url_logo'  => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:4096',
            ], [
                'nome.required' => 'O nome do parceiro é obrigatório.',
                'nome.max' => 'O nome do parceiro deve ter no máximo 255 caracteres.',
                'url_site.url' => 'A URL do site deve ser válida.',
                'descricao.max' => 'A descrição deve ter no máximo 500 caracteres.',
                'url_logo.mimes' => 'A logo deve ser uma imagem do tipo jpg, jpeg, png, webp ou gif.',
                'url_logo.max' => 'A logo deve ter no máximo 4MB.',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Só depois da validação, manipule a imagem
            if ($request->hasFile('url_logo')) {
                if ($parceiro->url_logo && Storage::disk('public')->exists($parceiro->url_logo)) {
                    Storage::disk('public')->delete($parceiro->url_logo);
                }

                $parceiro->url_logo = $request->file('url_logo')->store('parceiros', 'public');
            }

            $parceiro->nome      = $request->nome      ?? $parceiro->nome;
            $parceiro->url_site  = $request->url_site  ?? $parceiro->url_site;
            $parceiro->descricao = $request->descricao ?? $parceiro->descricao;

            $parceiro->save();

            return response()->json($parceiro->fresh(), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível atualizar o parceiro'], 500);
        }
    }

    /**
     * Deletar parceiro (soft delete) — remove a imagem do disco antes
     */
    public function destroy($id): JsonResponse
    {
        try {
            $parceiro = Parceiro::find($id);

            if (!$parceiro) {
                return response()->json(['error' => 'Parceiro não encontrado'], 404);
            }

            if ($parceiro->url_logo && Storage::disk('public')->exists($parceiro->url_logo)) {
                Storage::disk('public')->delete($parceiro->url_logo);
            }

            $parceiro->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível excluir o parceiro'], 500);
        }
    }

    /**
     * Restaurar parceiro deletado
     * Observação: se a imagem foi removida no destroy, ao restaurar o registro
     * o arquivo não estará mais disponível (mesmo comportamento do DocumentoController).
     */
    public function restore($id): JsonResponse
    {
        try {
            $parceiro = Parceiro::withTrashed()->find($id);

            if (!$parceiro) {
                return response()->json(['error' => 'Parceiro não encontrado'], 404);
            }

            if (!$parceiro->trashed()) {
                return response()->json(['error' => 'Parceiro já está ativo'], 400);
            }

            $parceiro->restore();

            return response()->json($parceiro, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível restaurar o parceiro'], 500);
        }
    }
}