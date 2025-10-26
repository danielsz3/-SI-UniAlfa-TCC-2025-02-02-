<?php

namespace App\Http\Controllers;

use App\Models\Ong;
use App\Models\ContatoOng;
use App\Traits\SearchIndex;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OngController extends Controller
{
    use SearchIndex;
    
    public function index(Request $request): JsonResponse
    {
        try {
            return $this->SearchIndex(
                $request,
                Ong::query(),
                'ongs',
                ['nome', 'descricao']
            );
        } catch (\Exception $e) {
            Log::error('Erro ao listar ongs: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Não foi possível carregar as ONGs'], 500);
        }
    }

    public function indexWithTrashed(): JsonResponse
    {
        $ongs = Ong::withTrashed()->get();

        return response()->json([
            'data'  => $ongs,
            'total' => $ongs->count()
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        // Se 'contatos' vier como string JSON (por exemplo via FormData), decodifica para array
        if ($request->has('contatos') && is_string($request->input('contatos'))) {
            $decoded = json_decode($request->input('contatos'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['contatos' => $decoded]);
            }
        }

        $validator = Validator::make($request->all(), [
            'nome'          => 'required|string|min:3|max:255',
            'cnpj'          => 'nullable|string|size:14|regex:/^[0-9]+$/',
            'razao_social'  => 'required|string|min:3|max:255',
            'descricao'     => 'nullable|string|max:1000',
            'imagem'        => 'nullable|url',

            // Endereço
            'cep'           => 'nullable|string|size:8|regex:/^[0-9]+$/',
            'logradouro'    => 'nullable|string|max:255',
            'numero'        => 'nullable|string|max:10',
            'complemento'   => 'nullable|string|max:100',
            'bairro'        => 'nullable|string|max:100',
            'cidade'        => 'nullable|string|max:100',
            'uf'            => 'nullable|string|size:2',

            // Dados bancários
            'banco'         => 'nullable|string|max:100',
            'agencia'       => 'nullable|string|max:10',
            'numero_conta'  => 'nullable|string|max:20',
            'tipo_conta'    => 'nullable|string|in:corrente,poupança',
            'chave_pix'     => 'nullable|string|max:255',

            // Contatos enviados do front
            'contatos'              => 'nullable|array',
            'contatos.*.tipo'       => 'required_with:contatos|in:telefone,email,redesocial,outro',
            'contatos.*.contato'    => 'required_with:contatos|string|max:255',
            'contatos.*.link'       => 'nullable|url',
            'contatos.*.descricao'  => 'nullable|string|max:255',
        ], [
            'nome.required' => 'O nome da ONG é obrigatório.',
            'nome.min' => 'O nome da ONG deve ter no mínimo 3 caracteres.',
            'nome.max' => 'O nome da ONG deve ter no máximo 255 caracteres.',
            
            'cnpj.size' => 'O CNPJ deve ter exatamente 14 números.',
            'cnpj.regex' => 'O CNPJ deve conter apenas números.',

            'razao_social.required' => 'A razão social da ONG é obrigatória.',
            'razao_social.min' => 'A razão social da ONG deve ter no mínimo 3 caracteres.',
            'razao_social.max' => 'A razão social da ONG deve ter no máximo 255 caracteres.',

            'descricao.max' => 'A descrição deve ter no máximo 1000 caracteres.',
            'imagem.url' => 'A URL da imagem deve ser válida.',

            'cep.size' => 'O CEP deve ter exatamente 8 números.',
            'cep.regex' => 'O CEP deve conter apenas números.',
            'logradouro.max' => 'O logradouro deve ter no máximo 255 caracteres.',
            'numero.max' => 'O número deve ter no máximo 10 caracteres.',
            'complemento.max' => 'O complemento deve ter no máximo 100 caracteres.',
            'bairro.max' => 'O bairro deve ter no máximo 100 caracteres.',
            'cidade.max' => 'A cidade deve ter no máximo 100 caracteres.',
            'uf.size' => 'O estado deve ter exatamente 2 caracteres.',

            'banco.max' => 'O banco deve ter no máximo 100 caracteres.',
            'agencia.max' => 'A agência deve ter no máximo 10 caracteres.',
            'numero_conta.max' => 'O número da conta deve ter no máximo 20 caracteres.',
            'tipo_conta.in' => 'O tipo de conta deve ser corrente ou poupança.',
            'chave_pix.max' => 'A chave PIX deve ter no máximo 255 caracteres.',

            'contatos.array' => 'Os contatos devem ser enviados como um array.',
            'contatos.*.tipo.in' => 'O tipo do contato deve ser telefone, email, redesocial ou outro.',
            'contatos.*.contato.required_with' => 'O valor do contato é obrigatório quando contatos for informado.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $ong = Ong::create($request->only([
                    'nome',
                    'cnpj',
                    'razao_social',
                    'descricao',
                    'imagem',
                    'cep',
                    'logradouro',
                    'numero',
                    'complemento',
                    'bairro',
                    'cidade',
                    'uf',
                    'banco',
                    'agencia',
                    'numero_conta',
                    'tipo_conta',
                    'chave_pix',
                ]));

                // Criar contatos se houver
                $contatos = $request->input('contatos', []);
                if (is_array($contatos) && !empty($contatos)) {
                    foreach ($contatos as $c) {
                        // Campos esperados: tipo, contato, link (opcional), descricao (opcional)
                        ContatoOng::create([
                            'id_ong'   => $ong->id,
                            'tipo'     => $c['tipo'] ?? null,
                            'contato'  => $c['contato'] ?? null,
                            'link'     => $c['link'] ?? null,
                            'descricao'=> $c['descricao'] ?? null,
                        ]);
                    }
                }

                return response()->json($ong->load('contatos'), 201);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao criar ONG: ' . $e->getMessage(), [
                'payload' => $request->all(),
                'exception' => $e
            ]);

            return response()->json([
                'error' => 'Erro ao criar ONG',
                'message' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $ong = Ong::with('contatos')->find($id);

        if (!$ong) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }

        return response()->json($ong, 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $ong = Ong::find($id);

        if (!$ong) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }

        // Se 'contatos' vier como string JSON (por exemplo via FormData), decodifica para array
        if ($request->has('contatos') && is_string($request->input('contatos'))) {
            $decoded = json_decode($request->input('contatos'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['contatos' => $decoded]);
            }
        }

        $validator = Validator::make($request->all(), [
            'nome'          => 'sometimes|required|string|min:3|max:255',
            'cnpj'          => 'nullable|string|size:14|regex:/^[0-9]+$/',
            'razao_social'  => 'sometimes|required|string|min:3|max:255',
            'descricao'     => 'nullable|string|max:1000',
            'imagem'        => 'nullable|url',

            // Endereço
            'cep'           => 'nullable|string|size:8|regex:/^[0-9]+$/',
            'logradouro'    => 'nullable|string|max:255',
            'numero'        => 'nullable|string|max:10',
            'complemento'   => 'nullable|string|max:100',
            'bairro'        => 'nullable|string|max:100',
            'cidade'        => 'nullable|string|max:100',
            'uf'            => 'nullable|string|size:2',

            // Dados bancários
            'banco'         => 'nullable|string|max:100',
            'agencia'       => 'nullable|string|max:10',
            'numero_conta'  => 'nullable|string|max:20',
            'tipo_conta'    => 'nullable|string|in:corrente,poupança',
            'chave_pix'     => 'nullable|string|max:255',

            // Contatos
            'contatos'              => 'nullable|array',
            'contatos.*.tipo'       => 'required_with:contatos|in:telefone,email,redesocial,outro',
            'contatos.*.contato'    => 'required_with:contatos|string|max:255',
            'contatos.*.link'       => 'nullable|url',
            'contatos.*.descricao'  => 'nullable|string|max:255',
        ], [
            // (reaproveite as mensagens do store, omitidas por brevidade)
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request, $ong) {
                $ong->update($request->only([
                    'nome',
                    'cnpj',
                    'razao_social',
                    'descricao',
                    'imagem',
                    'cep',
                    'logradouro',
                    'numero',
                    'complemento',
                    'bairro',
                    'cidade',
                    'uf',
                    'banco',
                    'agencia',
                    'numero_conta',
                    'tipo_conta',
                    'chave_pix',
                ]));

                // Se contatos foram enviados, substitui os atuais pelos novos
                if ($request->has('contatos')) {
                    // Apaga os contatos antigos (pode-se adaptar para update parcial, se preferir)
                    ContatoOng::where('id_ong', $ong->id)->delete();

                    $contatos = $request->input('contatos', []);
                    if (is_array($contatos) && !empty($contatos)) {
                        foreach ($contatos as $c) {
                            ContatoOng::create([
                                'id_ong'   => $ong->id,
                                'tipo'     => $c['tipo'] ?? null,
                                'contato'  => $c['contato'] ?? null,
                                'link'     => $c['link'] ?? null,
                                'descricao'=> $c['descricao'] ?? null,
                            ]);
                        }
                    }
                }

                return response()->json($ong->fresh('contatos'), 200);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar ONG: ' . $e->getMessage(), [
                'id' => $id,
                'payload' => $request->all(),
                'exception' => $e
            ]);

            return response()->json(['error' => 'Não foi possível atualizar a ONG'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $ong = Ong::find($id);

        if (!$ong) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }

        try {
            $ong->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível excluir a ONG'], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        $ong = Ong::withTrashed()->find($id);

        if (!$ong) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }

        if (!$ong->trashed()) {
            return response()->json(['error' => 'ONG já está ativa'], 400);
        }

        try {
            $ong->restore();

            return response()->json($ong, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível restaurar a ONG'], 500);
        }
    }
}