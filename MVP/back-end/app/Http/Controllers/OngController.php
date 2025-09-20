<?php

namespace App\Http\Controllers;

use App\Models\Ong;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class OngController extends Controller
{
    /**
     * Lista de ONGs (com paginação + filtros simples)
     * React Admin espera: { data: [...], total: number }
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('_limit', 10);
        $page    = $request->input('_page', 1);
        $sort    = $request->input('_sort', 'id_ong');
        $order   = strtolower($request->input('_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $filter  = json_decode($request->input('filter', '{}'), true) ?? [];

        $query = Ong::query();

        // filtros básicos
        if (!empty($filter)) {
            foreach ($filter as $field => $value) {
                if ($value) {
                    $query->where($field, 'like', "%{$value}%");
                }
            }
        }

        $query->orderBy($sort, $order);

        $ongs = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $ongs->getCollection()->map(fn($ong) => $this->formatOng($ong));

        return response()->json($data)
            ->header('X-Total-Count', $ongs->total())
            ->header('Access-Control-Expose-Headers', 'X-Total-Count');
    }

    /**
     * Mostrar uma ONG
     */
    public function show($id): JsonResponse
    {
        try {
            $ong = Ong::withTrashed()->findOrFail($id);

            return response()->json(['data' => $this->formatOng($ong)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }
    }

    /**
     * Criar ONG
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_usuario' => 'required|exists:usuarios,id_usuario|unique:ongs,id_usuario',
            'nome_ong'   => 'required|string|max:255',
            'cnpj'       => 'required|string|unique:ongs,cnpj|max:18',
            'descricao'  => 'nullable|string',
            'url_logo'   => 'nullable|url',
            'url_banner' => 'nullable|url',
            'telefone'   => 'nullable|string|max:11',
            'pix'        => 'nullable|string',
            'banco'      => 'nullable|string',
            'agencia'    => 'nullable|string',
            'conta'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ong = Ong::create($request->all());

        return response()->json(['data' => $this->formatOng($ong)], 201);
    }

    /**
     * Atualizar ONG
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $ong = Ong::withTrashed()->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nome_ong'   => 'sometimes|required|string|max:255',
                'cnpj'       => [
                    'sometimes','required','string','max:18',
                    Rule::unique('ongs','cnpj')->ignore($ong->id_ong, 'id_ong'),
                ],
                'descricao'  => 'nullable|string',
                'url_logo'   => 'nullable|url',
                'url_banner' => 'nullable|url',
                'telefone'   => 'nullable|string|max:11',
                'pix'        => 'nullable|string',
                'banco'      => 'nullable|string',
                'agencia'    => 'nullable|string',
                'conta'      => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $ong->fill($request->all())->save();

            return response()->json(['data' => $this->formatOng($ong)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }
    }

    /**
     * Inativar (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $ong = Ong::findOrFail($id);
            $ong->delete();

            return response()->json(['message' => 'ONG inativada com sucesso']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }
    }

    /**
     * Restaurar ONG
     */
    public function restore($id): JsonResponse
    {
        try {
            $ong = Ong::withTrashed()->findOrFail($id);
            $ong->restore();

            return response()->json(['message' => 'ONG restaurada com sucesso']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'ONG não encontrada'], 404);
        }
    }

    /**
     * Formatador padrão
     */
    private function formatOng($ong): array
    {
        return [
            'id_ong'      => $ong->id_ong,
            'id_usuario'  => $ong->id_usuario,
            'nome_ong'    => $ong->nome_ong,
            'cnpj'        => $ong->cnpj,
            'descricao'   => $ong->descricao,
            'url_logo'    => $ong->url_logo,
            'url_banner'  => $ong->url_banner,
            'telefone'    => $ong->telefone,
            'pix'         => $ong->pix,
            'banco'       => $ong->banco,
            'agencia'     => $ong->agencia,
            'conta'       => $ong->conta,
            'status'      => $ong->deleted_at ? 'inativo' : 'ativo',
            'created_at'  => $ong->created_at,
            'updated_at'  => $ong->updated_at,
        ];
    }
}