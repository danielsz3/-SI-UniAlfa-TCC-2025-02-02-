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
            $page    = $request->input('_page', 1);
            $sort    = $request->input('_sort', 'id');
            $order   = $request->input('_order', 'asc');
            $filter  = json_decode($request->input('filter', '{}'), true);

            $query = Ong::query();

            if (!empty($filter)) {
                foreach ($filter as $field => $value) {
                    if ($value === null || $value === '') continue;

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
            return response()->json(['error' => 'Não foi possível carregar as ONGs'], 500);
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
            'usuario_id'    => 'required|exists:usuarios,id',
            'nome_ong'      => 'required|string|min:3|max:255',
            'cnpj'          => 'required|string|size:14|regex:/^[0-9]+$/|unique:ongs,cnpj',
            'descricao'     => 'nullable|string|max:1000',
            'url_logo'      => 'nullable|url',
            'url_banner'    => 'nullable|url',
            'telefone'      => 'nullable|string|size:11|regex:/^[0-9]+$/',
            'pix'           => 'nullable|string|max:255',
            'banco'         => 'nullable|string|max:100',
            'agencia'       => 'nullable|string|max:10',
            'conta'         => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $ong = Ong::create($request->only([
                'usuario_id', 'nome_ong', 'cnpj', 'descricao', 'url_logo',
                'url_banner', 'telefone', 'pix', 'banco', 'agencia', 'conta'
            ]));

            return response()->json($ong, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível criar a ONG'], 500);
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
            'usuario_id' => 'sometimes|required|exists:usuarios,id',
            'nome_ong'   => 'sometimes|required|string|min:3|max:255',
            'cnpj'       => [
                'sometimes',
                'required',
                'string',
                'size:14',
                'regex:/^[0-9]+$/',
                Rule::unique('ongs')->ignore($ong->id)
            ],
            'descricao'  => 'nullable|string|max:1000',
            'url_logo'   => 'nullable|url',
            'url_banner' => 'nullable|url',
            'telefone'   => 'nullable|string|size:11|regex:/^[0-9]+$/',
            'pix'        => 'nullable|string|max:255',
            'banco'      => 'nullable|string|max:100',
            'agencia'    => 'nullable|string|max:10',
            'conta'      => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $ong->update($request->only([
                'usuario_id', 'nome_ong', 'cnpj', 'descricao', 'url_logo',
                'url_banner', 'telefone', 'pix', 'banco', 'agencia', 'conta'
            ]));

            return response()->json($ong->fresh(), 200);
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