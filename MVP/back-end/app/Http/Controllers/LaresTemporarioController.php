<?php

namespace App\Http\Controllers;

use App\Models\LarTemporario;
use App\Models\Endereco;
use App\Models\ImagemLarTemporario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\SearchIndex;

class LaresTemporarioController extends Controller
{
    use SearchIndex;

    /*
    |--------------------------------------------------------------------------
    | Listagem
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): JsonResponse
    {
        return $this->SearchIndex(
            $request,
            LarTemporario::with(['endereco', 'imagens']),
            'lares_temporarios',
            ['nome', 'data_nascimento', 'telefone']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Criação
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome'            => 'required|string|min:2|max:150',
            'data_nascimento' => 'required|date|before:today|after:1900-01-01',
            'telefone'        => 'required|string|size:11|regex:/^[0-9]+$/',
            'situacao'        => 'required|in:ativo,inativo',
            'experiencia'     => 'nullable|string|max:1000',

            // Endereço
            'endereco.cep'          => 'nullable|string|max:9',
            'endereco.logradouro'   => 'nullable|string|max:255',
            'endereco.numero'       => 'nullable|string|max:10',
            'endereco.complemento'  => 'nullable|string|max:100',
            'endereco.bairro'       => 'nullable|string|max:100',
            'endereco.cidade'       => 'nullable|string|max:100',
            'endereco.uf'           => 'nullable|string|max:2',

            // Imagens (até 10MB cada)
            'imagens'   => 'nullable|array',
            'imagens.*' => 'file|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $lar = LarTemporario::create($request->only([
                    'nome',
                    'data_nascimento',
                    'telefone',
                    'situacao',
                    'experiencia'
                ]));

                // Endereço
                if ($request->has('endereco') && !empty(array_filter($request->endereco))) {
                    $enderecoData = $request->endereco;
                    $enderecoData['lar_temporario_id'] = $lar->id;
                    Endereco::create($enderecoData);
                }

                // Upload de imagens
                if ($request->hasFile('imagens')) {
                    foreach ($request->file('imagens') as $file) {
                        $path = $file->store('lares_temporarios', 'public');
                        ImagemLarTemporario::create([
                            'id_lar_temporario' => $lar->id,
                            'url_imagem' => '/storage/' . $path
                        ]);
                    }
                }

                return response()->json($lar->load(['endereco', 'imagens']), 201);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao criar lar temporário: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao criar lar temporário'], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Detalhes
    |--------------------------------------------------------------------------
    */
    public function show($id): JsonResponse
    {
        $lar = LarTemporario::with(['endereco', 'imagens'])->find($id);

        if (!$lar) {
            return response()->json(['error' => 'Lar temporário não encontrado'], 404);
        }

        return response()->json($lar, 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Atualização
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id): JsonResponse
    {
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

            // Endereço
            'endereco.id'           => 'nullable|integer|exists:enderecos,id',
            'endereco.cep'          => 'nullable|string|max:9',
            'endereco.logradouro'   => 'nullable|string|max:255',
            'endereco.numero'       => 'nullable|string|max:10',
            'endereco.complemento'  => 'nullable|string|max:100',
            'endereco.bairro'       => 'nullable|string|max:100',
            'endereco.cidade'       => 'nullable|string|max:100',
            'endereco.uf'           => 'nullable|string|max:2',

            // Imagens
            'imagens'   => 'nullable|array',
            'imagens.*' => 'file|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request, $lar) {
                $lar->update($request->only([
                    'nome',
                    'data_nascimento',
                    'telefone',
                    'situacao',
                    'experiencia'
                ]));

                // Endereço
                if ($request->has('endereco') && !empty(array_filter($request->endereco))) {
                    $enderecoData = $request->endereco;

                    if (isset($enderecoData['id'])) {
                        $endereco = Endereco::where('id', $enderecoData['id'])
                            ->where('lar_temporario_id', $lar->id)
                            ->first();
                        if ($endereco) {
                            $endereco->update($enderecoData);
                        }
                    } else {
                        Endereco::where('lar_temporario_id', $lar->id)->delete();
                        $enderecoData['lar_temporario_id'] = $lar->id;
                        Endereco::create($enderecoData);
                    }
                }

                // Substitui todas as imagens se enviadas
                if ($request->hasFile('imagens')) {
                    ImagemLarTemporario::where('id_lar_temporario', $lar->id)->delete();

                    foreach ($request->file('imagens') as $file) {
                        $path = $file->store('lares_temporarios', 'public');
                        ImagemLarTemporario::create([
                            'id_lar_temporario' => $lar->id,
                            'url_imagem' => '/storage/' . $path
                        ]);
                    }
                }

                return response()->json($lar->fresh(['endereco', 'imagens']), 200);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar lar temporário: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao atualizar lar temporário'], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Exclusão
    |--------------------------------------------------------------------------
    */
    public function destroy($id): JsonResponse
    {
        $lar = LarTemporario::find($id);

        if (!$lar) {
            return response()->json(['error' => 'Lar temporário não encontrado'], 404);
        }

        $lar->delete(); // SoftDelete
        return response()->json(null, 204);
    }

    /*
    |--------------------------------------------------------------------------
    | Restauração
    |--------------------------------------------------------------------------
    */
    public function restore($id): JsonResponse
    {
        $lar = LarTemporario::withTrashed()->find($id);

        if (!$lar) {
            return response()->json(['error' => 'Lar temporário não encontrado'], 404);
        }

        if (!$lar->trashed()) {
            return response()->json(['error' => 'Lar já está ativo'], 400);
        }

        $lar->restore();
        return response()->json($lar->load(['endereco', 'imagens']), 200);
    }
}