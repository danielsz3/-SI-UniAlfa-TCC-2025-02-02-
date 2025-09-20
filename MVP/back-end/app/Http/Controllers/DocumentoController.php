<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class DocumentoController extends Controller
{
    // Listar documentos com paginação, ordenação e filtros
    public function index(Request $request): JsonResponse
    {
        // Detecta paginação (jsonServer ou simpleRest)
        $start = (int) $request->query('_start', 0);
        $end   = (int) $request->query('_end', 0);
        $perPage = (int) $request->input('_limit', ($end > 0 ? ($end - $start) : 10));
        $page    = (int) $request->input('_page', ($perPage > 0 ? intval($start / $perPage) + 1 : 1));

        // Ordenação
        $sort  = $request->query('_sort', 'id');
        $order = $request->query('_order', 'ASC');

        $query = Documento::query();

        // 🚀 aplica todos os filtros vindos como query params
        foreach ($request->query() as $field => $value) {
            if (in_array($field, ['_start','_end','_sort','_order','_page','_limit'])) {
                continue;
            }

            if ($value === null || $value === '') continue;

            // Range automático: campo_from / campo_to
            if (preg_match('/(.+)_from$/', $field, $matches)) {
                $query->where($matches[1], '>=', $value);
                continue;
            }
            if (preg_match('/(.+)_to$/', $field, $matches)) {
                $query->where($matches[1], '<=', $value);
                continue;
            }

            // LIKE nos campos textuais
            if (in_array($field, ['titulo','categoria','descricao'])) {
                $query->where($field, 'like', '%' . $value . '%');
            } else {
                $query->where($field, $value);
            }
        }

        // Ordenação
        $query->orderBy($sort, $order);

        $documentos = $query->paginate($perPage, ['*'], 'page', $page);

        return response()
            ->json($documentos->items())
            ->header('X-Total-Count', $documentos->total())
            ->header('Access-Control-Expose-Headers', 'X-Total-Count');
    }

    // Criar novo documento com upload
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'documento' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Upload do arquivo
        $path = $request->hasFile('documento') 
            ? $request->file('documento')->store('documentos', 'public')
            : null;

        $documento = Documento::create([
            'titulo' => $request->titulo,
            'categoria' => $request->categoria,
            'descricao' => $request->descricao,
            'documento' => $path,
        ]);

        return response()->json($documento, 201);
    }

    // Mostrar um documento
    public function show($id)
    {
        return response()->json(Documento::findOrFail($id), 200);
    }

    // Atualizar documento e arquivo
    public function update(Request $request, $id)
    {
        $documento = Documento::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'titulo' => 'sometimes|required|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'documento' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Se vier novo arquivo, troca
        if ($request->hasFile('documento')) {
            if ($documento->documento && Storage::disk('public')->exists($documento->documento)) {
                Storage::disk('public')->delete($documento->documento);
            }
            $documento->documento = $request->file('documento')->store('documentos', 'public');
        }

        $documento->titulo = $request->titulo ?? $documento->titulo;
        $documento->categoria = $request->categoria ?? $documento->categoria;
        $documento->descricao = $request->descricao ?? $documento->descricao;
        $documento->save();

        return response()->json($documento, 200);
    }

    // Deletar documento
    public function destroy($id)
    {
        $documento = Documento::findOrFail($id);

        if ($documento->documento && Storage::disk('public')->exists($documento->documento)) {
            Storage::disk('public')->delete($documento->documento);
        }

        $documento->delete();

        return response()->json(['message' => 'Documento deletado com sucesso'], 200);
    }
}