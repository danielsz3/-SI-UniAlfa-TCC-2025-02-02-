<?php

namespace App\Http\Controllers;

use App\Models\Parceiro;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ParceiroController extends Controller
{
   
    public function index(Request $request): JsonResponse
{
    // Paginação padrão do SimpleRest (React-Admin)
    $perPage = (int) $request->input('_limit', 10);
    $page    = (int) $request->input('_page', 1);

    // Ordenação
    $sort  = $request->input('_sort', 'id_parceiro');
    $order = $request->input('_order', 'ASC');
    
    // Se o provider mandar "filter={...}" como JSON na URL
    $filter = json_decode($request->input('filter', '{}'), true);

    $query = Parceiro::query();

    // 🚀 aplica filtros dinâmicos se vier "filter={}"
    if (!empty($filter)) {
        foreach ($filter as $field => $value) {
            if ($value === null || $value === '') continue;

            // Suporte a ranges: field_from / field_to
            if (is_array($value) && isset($value['from'])) {
                $query->where($field, '>=', $value['from']);
                if (isset($value['to'])) {
                    $query->where($field, '<=', $value['to']);
                }
                continue;
            }

            // Suporte a lista separada por vírgula
            if (is_string($value) && str_contains($value, ',')) {
                $query->whereIn($field, explode(',', $value));
                continue;
            }

            // Filtros textuais comuns
            if (in_array($field, ['nome','email','telefone'])) {
                $query->where($field, 'like', "%{$value}%");
            } else {
                $query->where($field, $value);
            }
        }
    }

    // 🔽 ordenação
    $query->orderBy($sort, $order);

    $parceiros = $query->paginate($perPage, ['*'], 'page', $page);

    return response()
        ->json($parceiros->items())
        ->header('X-Total-Count', $parceiros->total()) // ⚠️ obrigatório p/ React-Admin
        ->header('Access-Control-Expose-Headers', 'X-Total-Count');
}

    public function indexWithTrashed(): JsonResponse
    {
        $parceiros = Parceiro::withTrashed()->get();

        return response()->json([
            'data' => $parceiros,
            'total' => $parceiros->count()
        ]);
    }


    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nome_parceiro' => 'required|string|max:255',
            'url_site' => 'nullable|string',
            'url_logo' => 'nullable|string',
            'descricao' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $parceiro = Parceiro::create($request->all());

        return response()->json(['data' => $parceiro], 201);
    }


    public function show($id): JsonResponse
    {
        $parceiro = Parceiro::find($id);

        if (!$parceiro) {
            return response()->json(['error' => 'Parceiro não encontrado'], 404);
        }

        return response()->json(['data' => $parceiro]);
    }

 
    public function update(Request $request, $id): JsonResponse
    {
        $parceiro = Parceiro::find($id);

        if (!$parceiro) {
            return response()->json(['error' => 'Parceiro não encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nome_parceiro' => 'sometimes|required|string|max:255',
            'url_site' => 'nullable|string',
            'url_logo' => 'nullable|string',
            'descricao' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $parceiro->fill($request->all());
        $parceiro->save();

        return response()->json(['data' => $parceiro]);
    }


    public function destroy($id): JsonResponse
    {
        $parceiro = Parceiro::find($id);

        if (!$parceiro) {
            return response()->json(['error' => 'Parceiro não encontrado'], 404);
        }

        $parceiro->delete();

        return response()->json(['data' => $parceiro]);
    }


    public function restore($id): JsonResponse
    {
        $parceiro = Parceiro::withTrashed()->find($id);

        if (!$parceiro) {
            return response()->json(['error' => 'Parceiro não encontrado'], 404);
        }

        $parceiro->restore();

        return response()->json(['data' => $parceiro]);
    }
}
