<?php

namespace App\Http\Controllers;

use App\Models\Ong;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OngController extends Controller
{
    /**
     * Lista de ONGs com paginação e filtros
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('_limit', 10);
            $page = $request->input('_page', 1);
            $sort = $request->input('_sort', 'id');
            $order = $request->input('_order', 'asc');
            $filter = json_decode($request->input('filter', '{}'), true);

            $query = Ong::query();

            // Filtros dinâmicos
            if (!empty($filter)) {
                foreach ($filter as $field => $value) {
                    if ($value === null || $value === '') continue;

                    // Campos textuais com LIKE
                    if (in_array($field, ['nome_ong', 'descricao', 'cnpj', 'telefone'])) {
                        $query->where($field, 'like', "%{$value}%");
                    } else {
                        $query->where($field, $value);
                    }
                }
            }

            $query->orderBy($sort, $order);

            $ongs = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json($ongs->items())
                ->header('X-Total-Count', $ongs->total())
                ->header('Access-Control-Expose-Headers', 'X-Total-Count');

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar as ONGs'
            ], 500);
        }
    }

    /**
     * Listar ONGs incluindo as deletadas (admin)
     */
    public function indexWithTrashed(): JsonResponse
    {
        try {
            $ongs = Ong::withTrashed()->get();

            return response()->json([
                'message' => 'Lista de ONGs (incluindo deletadas)',
                'data' => $ongs,
                'total' => $ongs->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar as ONGs'
            ], 500);
        }
    }

    /**
     * Criar uma nova ONG
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|exists:usuarios,id',
            'nome_ong' => 'required|string|min:3|max:255',
            'cnpj' => 'required|string|size:14|regex:/^[0-9]+$/|unique:ongs,cnpj',
            'descricao' => 'nullable|string|max:1000',
            'url_logo' => 'nullable|url',
            'url_banner' => 'nullable|url',
            'telefone' => 'nullable|string|size:11|regex:/^[0-9]+$/',
            'pix' => 'nullable|string|max:255',
            'banco' => 'nullable|string|max:100',
            'agencia' => 'nullable|string|max:10',
            'conta' => 'nullable|string|max:20',
        ], [
            // Mensagens personalizadas
            'usuario_id.required' => 'O usuário responsável é obrigatório',
            'usuario_id.exists' => 'O usuário especificado não existe',
            
            'nome_ong.required' => 'O nome da ONG é obrigatório',
            'nome_ong.min' => 'O nome da ONG deve ter pelo menos 3 caracteres',
            'nome_ong.max' => 'O nome da ONG não pode ter mais de 255 caracteres',
            
            'cnpj.required' => 'O CNPJ é obrigatório',
            'cnpj.size' => 'O CNPJ deve ter exatamente 14 dígitos',
            'cnpj.regex' => 'O CNPJ deve conter apenas números',
            'cnpj.unique' => 'Este CNPJ já está cadastrado',
            
            'descricao.max' => 'A descrição não pode ter mais de 1000 caracteres',
            
            'url_logo.url' => 'Digite uma URL válida para a logo',
            'url_banner.url' => 'Digite uma URL válida para o banner',
            
            'telefone.size' => 'O telefone deve ter exatamente 11 dígitos',
            'telefone.regex' => 'O telefone deve conter apenas números',
            
            'pix.max' => 'A chave PIX não pode ter mais de 255 caracteres',
            'banco.max' => 'O nome do banco não pode ter mais de 100 caracteres',
            'agencia.max' => 'A agência não pode ter mais de 10 caracteres',
            'conta.max' => 'A conta não pode ter mais de 20 caracteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Dados inválidos',
                'message' => 'Verifique os campos e tente novamente',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ong = Ong::create($request->only([
                'usuario_id', 'nome_ong', 'cnpj', 'descricao', 'url_logo', 
                'url_banner', 'telefone', 'pix', 'banco', 'agencia', 'conta'
            ]));

            return response()->json([
                'message' => 'ONG criada com sucesso!',
                'data' => $ong
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível criar a ONG'
            ], 500);
        }
    }

    /**
     * Exibir uma ONG específica
     */
    public function show($id): JsonResponse
    {
        try {
            $ong = Ong::find($id);

            if (!$ong) {
                return response()->json([
                    'error' => 'ONG não encontrada',
                    'message' => 'A ONG solicitada não existe'
                ], 404);
            }

            return response()->json([
                'message' => 'ONG encontrada',
                'data' => $ong
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar a ONG'
            ], 500);
        }
    }

    /**
     * Atualizar uma ONG
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $ong = Ong::find($id);

            if (!$ong) {
                return response()->json([
                    'error' => 'ONG não encontrada',
                    'message' => 'A ONG que você está tentando atualizar não existe'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'usuario_id' => 'sometimes|required|exists:usuarios,id',
                'nome_ong' => 'sometimes|required|string|min:3|max:255',
                'cnpj' => [
                    'sometimes',
                    'required',
                    'string',
                    'size:14',
                    'regex:/^[0-9]+$/',
                    Rule::unique('ongs')->ignore($ong->id)
                ],
                'descricao' => 'nullable|string|max:1000',
                'url_logo' => 'nullable|url',
                'url_banner' => 'nullable|url',
                'telefone' => 'nullable|string|size:11|regex:/^[0-9]+$/',
                'pix' => 'nullable|string|max:255',
                'banco' => 'nullable|string|max:100',
                'agencia' => 'nullable|string|max:10',
                'conta' => 'nullable|string|max:20',
            ], [
                // Mensagens personalizadas (mesmas do store)
                'usuario_id.required' => 'O usuário responsável é obrigatório',
                'usuario_id.exists' => 'O usuário especificado não existe',
                
                'nome_ong.required' => 'O nome da ONG é obrigatório',
                'nome_ong.min' => 'O nome da ONG deve ter pelo menos 3 caracteres',
                'nome_ong.max' => 'O nome da ONG não pode ter mais de 255 caracteres',
                
                'cnpj.required' => 'O CNPJ é obrigatório',
                'cnpj.size' => 'O CNPJ deve ter exatamente 14 dígitos',
                'cnpj.regex' => 'O CNPJ deve conter apenas números',
                'cnpj.unique' => 'Este CNPJ já está cadastrado',
                
                'descricao.max' => 'A descrição não pode ter mais de 1000 caracteres',
                
                'url_logo.url' => 'Digite uma URL válida para a logo',
                'url_banner.url' => 'Digite uma URL válida para o banner',
                
                'telefone.size' => 'O telefone deve ter exatamente 11 dígitos',
                'telefone.regex' => 'O telefone deve conter apenas números',
                
                'pix.max' => 'A chave PIX não pode ter mais de 255 caracteres',
                'banco.max' => 'O nome do banco não pode ter mais de 100 caracteres',
                'agencia.max' => 'A agência não pode ter mais de 10 caracteres',
                'conta.max' => 'A conta não pode ter mais de 20 caracteres',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Dados inválidos',
                    'message' => 'Verifique os campos e tente novamente',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ong->update($request->only([
                'usuario_id', 'nome_ong', 'cnpj', 'descricao', 'url_logo', 
                'url_banner', 'telefone', 'pix', 'banco', 'agencia', 'conta'
            ]));

            return response()->json([
                'message' => 'ONG atualizada com sucesso!',
                'data' => $ong->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível atualizar a ONG'
            ], 500);
        }
    }

    /**
     * Deletar uma ONG (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $ong = Ong::find($id);

            if (!$ong) {
                return response()->json([
                    'error' => 'ONG não encontrada',
                    'message' => 'A ONG que você está tentando excluir não existe'
                ], 404);
            }

            $ong->delete();

            return response()->json([
                'message' => 'ONG excluída com sucesso!',
                'data' => $ong
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível excluir a ONG'
            ], 500);
        }
    }

    /**
     * Restaurar uma ONG deletada
     */
    public function restore($id): JsonResponse
    {
        try {
            $ong = Ong::withTrashed()->find($id);

            if (!$ong) {
                return response()->json([
                    'error' => 'ONG não encontrada',
                    'message' => 'A ONG que você está tentando restaurar não existe'
                ], 404);
            }

            if (!$ong->trashed()) {
                return response()->json([
                    'error' => 'ONG já está ativa',
                    'message' => 'Esta ONG não precisa ser restaurada'
                ], 400);
            }

            $ong->restore();

            return response()->json([
                'message' => 'ONG restaurada com sucesso!',
                'data' => $ong
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível restaurar a ONG'
            ], 500);
        }
    }

    /**
     * Buscar ONGs por usuário
     */
    public function byUser($userId): JsonResponse
    {
        try {
            $ongs = Ong::where('usuario_id', $userId)->get();

            return response()->json([
                'message' => 'ONGs do usuário encontradas',
                'data' => $ongs,
                'total' => $ongs->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível carregar as ONGs do usuário'
            ], 500);
        }
    }
}