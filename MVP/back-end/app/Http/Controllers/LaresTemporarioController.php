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

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar os lares temporários'
            ], 500);
        }
    }

    /**
     * Criar novo lar temporário
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data_nascimento' => 'required|date|before:today|after:1900-01-01',
            'telefone'        => 'required|string|size:11|regex:/^[0-9]+$/',
            'situacao'        => 'required|in:ativo,inativo',
            'experiencia'     => 'nullable|string|max:1000',
        ], [
            'data_nascimento.required' => 'A data de nascimento é obrigatória',
            'data_nascimento.date' => 'Digite uma data válida',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje',
            'data_nascimento.after' => 'Digite uma data de nascimento válida',
            
            'telefone.required' => 'O telefone é obrigatório',
            'telefone.size' => 'O telefone deve ter exatamente 11 dígitos',
            'telefone.regex' => 'O telefone deve conter apenas números',
            
            'situacao.required' => 'A situação é obrigatória',
            'situacao.in' => 'A situação deve ser "ativo" ou "inativo"',
            
            'experiencia.max' => 'A experiência não pode ter mais de 1000 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Dados inválidos',
                'message' => 'Verifique os campos e tente novamente',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $lar = LarTemporario::create($request->only([
                'data_nascimento', 'telefone', 'situacao', 'experiencia'
            ]));

            return response()->json([
                'message' => 'Lar temporário criado com sucesso!',
                'data' => $lar
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível criar o lar temporário'
            ], 500);
        }
    }

    /**
     * Exibir um lar temporário com seus endereços
     */
    public function show($id): JsonResponse
    {
        try {
            $lar = LarTemporario::with('enderecos')->find($id);

            if (!$lar) {
                return response()->json([
                    'error' => 'Lar temporário não encontrado',
                    'message' => 'O lar temporário solicitado não existe'
                ], 404);
            }

            return response()->json([
                'message' => 'Lar temporário encontrado',
                'data' => $lar
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar o lar temporário'
            ], 500);
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
                return response()->json([
                    'error' => 'Lar temporário não encontrado',
                    'message' => 'O lar temporário que você está tentando atualizar não existe'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'data_nascimento' => 'sometimes|required|date|before:today|after:1900-01-01',
                'telefone'        => 'sometimes|required|string|size:11|regex:/^[0-9]+$/',
                'situacao'        => 'sometimes|required|in:ativo,inativo',
                'experiencia'     => 'nullable|string|max:1000',
            ], [
                'data_nascimento.required' => 'A data de nascimento é obrigatória',
                'data_nascimento.date' => 'Digite uma data válida',
                'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje',
                'data_nascimento.after' => 'Digite uma data de nascimento válida',
                
                'telefone.required' => 'O telefone é obrigatório',
                'telefone.size' => 'O telefone deve ter exatamente 11 dígitos',
                'telefone.regex' => 'O telefone deve conter apenas números',
                
                'situacao.required' => 'A situação é obrigatória',
                'situacao.in' => 'A situação deve ser "ativo" ou "inativo"',
                
                'experiencia.max' => 'A experiência não pode ter mais de 1000 caracteres',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dados inválidos',
                    'message' => 'Verifique os campos e tente novamente',
                    'errors' => $validator->errors()
                ], 422);
            }

            $lar->update($request->only([
                'data_nascimento', 'telefone', 'situacao', 'experiencia'
            ]));

            return response()->json([
                'message' => 'Lar temporário atualizado com sucesso!',
                'data' => $lar->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível atualizar o lar temporário'
            ], 500);
        }
    }

    /**
     * Deletar lar temporário (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $lar = LarTemporario::find($id);

            if (!$lar) {
                return response()->json([
                    'error' => 'Lar temporário não encontrado',
                    'message' => 'O lar temporário que você está tentando excluir não existe'
                ], 404);
            }

            $lar->delete();

            return response()->json([
                'message' => 'Lar temporário excluído com sucesso!',
                'data' => $lar
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível excluir o lar temporário'
            ], 500);
        }
    }
}