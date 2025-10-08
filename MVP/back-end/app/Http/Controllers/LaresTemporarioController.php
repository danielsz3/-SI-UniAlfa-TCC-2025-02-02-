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
use Illuminate\Support\Facades\Storage;
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
        // Decodifica o campo 'endereco' (string JSON) para array, pois pode vir via FormData
        if ($request->has('endereco') && is_string($request->input('endereco'))) {
            $request->merge(['endereco' => json_decode($request->input('endereco'), true)]);
        }

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
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter no mínimo 2 caracteres.',
            'nome.max' => 'O nome deve ter no máximo 150 caracteres.',

            'data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'data_nascimento.date' => 'A data de nascimento deve ser válida.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'data_nascimento.after' => 'A data de nascimento deve ser posterior a 01/01/1900.',

            'telefone.required' => 'O telefone é obrigatório.',
            'telefone.size' => 'O telefone deve ter exatamente 11 números.',
            'telefone.regex' => 'O telefone deve conter apenas números.',

            'situacao.required' => 'A situação é obrigatória.',
            'situacao.in' => 'A situação deve ser "ativo" ou "inativo".',

            'experiencia.max' => 'A experiência deve ter no máximo 1000 caracteres.',

            'endereco.cep.max' => 'O CEP deve ter no máximo 9 caracteres.',
            'endereco.logradouro.max' => 'O logradouro deve ter no máximo 255 caracteres.',
            'endereco.numero.max' => 'O número deve ter no máximo 10 caracteres.',
            'endereco.complemento.max' => 'O complemento deve ter no máximo 100 caracteres.',
            'endereco.bairro.max' => 'O bairro deve ter no máximo 100 caracteres.',
            'endereco.cidade.max' => 'A cidade deve ter no máximo 100 caracteres.',
            'endereco.uf.max' => 'A UF deve ter no máximo 2 caracteres.',

            'imagens.*.image' => 'Cada arquivo enviado em imagens deve ser uma imagem válida.',
            'imagens.*.mimes' => 'As imagens devem ser do tipo: jpeg, png, jpg ou gif.',
            'imagens.*.max' => 'Cada imagem deve ter no máximo 10MB.',
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
                        [$width, $height] = getimagesize($file->getRealPath()) ?: [null, null];
                        ImagemLarTemporario::create([
                            'id_lar_temporario' => $lar->id,
                            'url_imagem' => $path,
                            'width' => $width,
                            'height' => $height,
                        ]);
                    }
                }

                return response()->json($lar->load(['endereco', 'imagens']), 201);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao criar lar temporário: ' . $e->getMessage(), [
                'exception' => $e
            ]);
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

        // Decodifica o campo 'endereco' (string JSON) para array, caso venha por FormData
        if ($request->has('endereco') && is_string($request->input('endereco'))) {
            $request->merge(['endereco' => json_decode($request->input('endereco'), true)]);
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
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter no mínimo 2 caracteres.',
            'nome.max' => 'O nome deve ter no máximo 150 caracteres.',

            'data_nascimento.date' => 'A data de nascimento deve ser válida.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'data_nascimento.after' => 'A data de nascimento deve ser posterior a 01/01/1900.',

            'telefone.size' => 'O telefone deve ter exatamente 11 números.',
            'telefone.regex' => 'O telefone deve conter apenas números.',

            'situacao.in' => 'A situação deve ser "ativo" ou "inativo".',

            'experiencia.max' => 'A experiência deve ter no máximo 1000 caracteres.',

            'endereco.id.exists' => 'O endereço informado não existe.',
            'endereco.cep.max' => 'O CEP deve ter no máximo 9 caracteres.',
            'endereco.logradouro.max' => 'O logradouro deve ter no máximo 255 caracteres.',
            'endereco.numero.max' => 'O número deve ter no máximo 10 caracteres.',
            'endereco.complemento.max' => 'O complemento deve ter no máximo 100 caracteres.',
            'endereco.bairro.max' => 'O bairro deve ter no máximo 100 caracteres.',
            'endereco.cidade.max' => 'A cidade deve ter no máximo 100 caracteres.',
            'endereco.uf.max' => 'A UF deve ter no máximo 2 caracteres.',

            'imagens.*.image' => 'Cada arquivo enviado em imagens deve ser uma imagem válida.',
            'imagens.*.mimes' => 'As imagens devem ser do tipo: jpeg, png, jpg ou gif.',
            'imagens.*.max' => 'Cada imagem deve ter no máximo 10MB.',
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
                        } else {
                            // se id informado não pertence a este lar, criamos um novo endereço vinculado
                            $enderecoData['lar_temporario_id'] = $lar->id;
                            Endereco::create($enderecoData);
                        }
                    } else {
                        // remove endereços anteriores e cria novo (mantendo seu comportamento original)
                        Endereco::where('lar_temporario_id', $lar->id)->delete();
                        $enderecoData['lar_temporario_id'] = $lar->id;
                        Endereco::create($enderecoData);
                    }
                }

                // Substitui todas as imagens se enviadas
                if ($request->hasFile('imagens')) {
                    // Apagar arquivos antigos do storage
                    $oldImagens = ImagemLarTemporario::where('id', $lar->id)->get();
                    foreach ($oldImagens as $imagem) {
                        if ($imagem->url_imagem) {
                            $oldPath = ltrim(str_replace('/storage/', '', $imagem->url_imagem), '/');
                            if (Storage::disk('public')->exists($oldPath)) {
                                Storage::disk('public')->delete($oldPath);
                            }
                        }
                    }
                    // Apagar registros antigos
                    ImagemLarTemporario::where('id', $lar->id)->delete();

                    // Salvar novas imagens
                    foreach ($request->file('imagens') as $file) {
                        $path = $file->store('lares_temporarios', 'public');
                        [$width, $height] = getimagesize($file->getRealPath()) ?: [null, null];
                        ImagemLarTemporario::create([
                            'id_lar_temporario' => $lar->id,
                            'url_imagem' => $path,
                            'width' => $width,
                            'height' => $height,
                        ]);
                    }
                }

                return response()->json($lar->fresh(['endereco', 'imagens']), 200);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar lar temporário: ' . $e->getMessage(), [
                'exception' => $e
            ]);
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
        $lar = LarTemporario::with(['imagens'])->find($id);

        if (!$lar) {
            return response()->json(['error' => 'Lar temporário não encontrado'], 404);
        }

        try {
            // remover arquivos do storage (caso existam)
            foreach ($lar->imagens as $imagem) {
                if ($imagem->url_imagem) {
                    $oldPath = ltrim(str_replace('/storage/', '', $imagem->url_imagem), '/');
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
            }

            $lar->delete(); // SoftDelete

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar lar temporário: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['error' => 'Erro ao deletar lar temporário'], 500);
        }
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

        try {
            $lar->restore();
            return response()->json($lar->load(['endereco', 'imagens']), 200);
        } catch (\Exception $e) {
            Log::error('Erro ao restaurar lar temporário: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['error' => 'Erro ao restaurar lar temporário'], 500);
        }
    }
}