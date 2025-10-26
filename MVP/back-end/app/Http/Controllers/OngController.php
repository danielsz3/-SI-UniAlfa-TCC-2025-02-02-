<?php

namespace App\Http\Controllers;

use App\Models\Ong;
use App\Traits\SearchIndex;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Log;

class OngController extends Controller
{
    use SearchIndex;
    /**
     * Lista de ONGs com paginação e filtros
     */
    public function index(Request $request): JsonResponse
    {
        try {
            return $this->SearchIndex(
                $request,
                Ong::with('imagens'),
                'ongs',
                ['nome', 'descricao']
            );
        } catch (\Exception $e) {
            Log::error('Erro ao listar ongs: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Não foi possível carregar os animais'], 500);
        }
    }

    /**
     * Listar ONGs incluindo deletadas
     */
    public function indexWithTrashed(): JsonResponse
    {
        $ongs = Ong::withTrashed()->get();

        return response()->json([
            'data'  => $ongs,
            'total' => $ongs->count()
        ], 200);
    }

    /**
     * Criar ONG
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome'          => 'required|string|min:3|max:255',
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
            'estado'        => 'nullable|string|size:2',
            'pais'          => 'nullable|string|max:100',

            // Dados bancários
            'banco'         => 'nullable|string|max:100',
            'agencia'       => 'nullable|string|max:10',
            'numero_conta'  => 'nullable|string|max:20',
            'tipo_conta'    => 'nullable|string|in:corrente,poupança',
            'chave_pix'     => 'nullable|string|max:255',
        ], [
            // Mensagens personalizadas para validações
            'nome.required' => 'O nome da ONG é obrigatório.',
            'nome.min' => 'O nome da ONG deve ter no mínimo 3 caracteres.',
            'nome.max' => 'O nome da ONG deve ter no máximo 255 caracteres.',

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
            'estado.size' => 'O estado deve ter exatamente 2 caracteres.',
            'pais.max' => 'O país deve ter no máximo 100 caracteres.',

            'banco.max' => 'O banco deve ter no máximo 100 caracteres.',
            'agencia.max' => 'A agência deve ter no máximo 10 caracteres.',
            'numero_conta.max' => 'O número da conta deve ter no máximo 20 caracteres.',
            'tipo_conta.in' => 'O tipo de conta deve ser corrente ou poupança.',
            'chave_pix.max' => 'A chave PIX deve ter no máximo 255 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $ong = Ong::create($request->only([
                'nome',
                'razao_social',
                'descricao',
                'imagem',
                'cep',
                'logradouro',
                'numero',
                'complemento',
                'bairro',
                'cidade',
                'estado',
                'pais',
                'banco',
                'agencia',
                'numero_conta',
                'tipo_conta',
                'chave_pix',
            ]));

            return response()->json($ong, 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao criar ONG',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibir ONG
     */
    public function show($id): JsonResponse
    {
        $ong = Ong::find($id);

        if (!$ong) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }

        return response()->json($ong, 200);
    }

    /**
     * Atualizar ONG
     */
    public function update(Request $request, $id): JsonResponse
    {
        $ong = Ong::find($id);

        if (!$ong) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nome'          => 'sometimes|required|string|min:3|max:255',
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
            'estado'        => 'nullable|string|size:2',
            'pais'          => 'nullable|string|max:100',

            // Dados bancários
            'banco'         => 'nullable|string|max:100',
            'agencia'       => 'nullable|string|max:10',
            'numero_conta'  => 'nullable|string|max:20',
            'tipo_conta'    => 'nullable|string|in:corrente,poupança',
            'chave_pix'     => 'nullable|string|max:255',
        ], [
            // Mensagens personalizadas para validações
            'nome.required' => 'O nome da ONG é obrigatório.',
            'nome.min' => 'O nome da ONG deve ter no mínimo 3 caracteres.',
            'nome.max' => 'O nome da ONG deve ter no máximo 255 caracteres.',

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
            'estado.size' => 'O estado deve ter exatamente 2 caracteres.',
            'pais.max' => 'O país deve ter no máximo 100 caracteres.',

            'banco.max' => 'O banco deve ter no máximo 100 caracteres.',
            'agencia.max' => 'A agência deve ter no máximo 10 caracteres.',
            'numero_conta.max' => 'O número da conta deve ter no máximo 20 caracteres.',
            'tipo_conta.in' => 'O tipo de conta deve ser corrente ou poupança.',
            'chave_pix.max' => 'A chave PIX deve ter no máximo 255 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $ong->update($request->only([
                'nome',
                'razao_social',
                'descricao',
                'imagem',
                'cep',
                'logradouro',
                'numero',
                'complemento',
                'bairro',
                'cidade',
                'estado',
                'pais',
                'banco',
                'agencia',
                'numero_conta',
                'tipo_conta',
                'chave_pix',
            ]));

            return response()->json($ong, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível atualizar a ONG'], 500);
        }
    }

    /**
     * Deletar ONG (soft delete)
     */
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

    /**
     * Restaurar ONG (soft delete)
     */
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
