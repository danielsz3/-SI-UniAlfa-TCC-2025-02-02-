<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class DocumentoController extends Controller
{
    /**
     * Listar documentos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $start   = (int) $request->query('_start', 0);
            $end     = (int) $request->query('_end', 0);
            $perPage = (int) $request->input('_limit', ($end > 0 ? ($end - $start) : 10));
            $page    = (int) $request->input('_page', ($perPage > 0 ? intval($start / $perPage) + 1 : 1));

            $sort  = $request->query('_sort', 'id');
            $order = $request->query('_order', 'ASC');

            $query = Documento::query();

            foreach ($request->query() as $field => $value) {
                if (in_array($field, ['_start','_end','_sort','_order','_page','_limit'])) continue;
                if ($value === null || $value === '') continue;

                if (preg_match('/(.+)_from$/', $field, $matches)) {
                    $query->where($matches[1], '>=', $value);
                    continue;
                }
                if (preg_match('/(.+)_to$/', $field, $matches)) {
                    $query->where($matches[1], '<=', $value);
                    continue;
                }

                if (in_array($field, ['titulo','categoria','descricao'])) {
                    $query->where($field, 'like', '%' . $value . '%');
                } else {
                    $query->where($field, $value);
                }
            }

            $query->orderBy($sort, $order);

            $documentos = $query->paginate($perPage, ['*'], 'page', $page);

            return response()
                ->json($documentos->items())
                ->header('X-Total-Count', $documentos->total())
                ->header('Access-Control-Expose-Headers', 'X-Total-Count');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível carregar os documentos'], 500);
        }
    }

    /**
     * Criar novo documento
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'titulo'    => 'required|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'arquivo'   => 'required|file|mimes:pdf,doc,docx,jpg,png|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $file = $request->file('arquivo');
            $path = $file->store('documentos', 'public');

            $documento = Documento::create([
                'titulo'    => $request->titulo,
                'categoria' => $request->categoria,
                'descricao' => $request->descricao,
                'arquivo'   => $path,
                'tipo'      => $file->getClientMimeType(),
                'tamanho'   => $file->getSize(),
            ]);

            return response()->json($documento, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível salvar o documento'], 500);
        }
    }

    /**
     * Mostrar documento
     */
    public function show($id): JsonResponse
    {
        try {
            $documento = Documento::find($id);

            if (!$documento) {
                return response()->json(['error' => 'Documento não encontrado'], 404);
            }

            return response()->json($documento, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível carregar o documento'], 500);
        }
    }

    /**
     * Atualizar documento
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $documento = Documento::find($id);

            if (!$documento) {
                return response()->json(['error' => 'Documento não encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'titulo'    => 'sometimes|required|string|max:255',
                'categoria' => 'nullable|string|max:255',
                'descricao' => 'nullable|string|max:1000',
                'arquivo'   => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:4096',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if ($request->hasFile('arquivo')) {
                if ($documento->arquivo && Storage::disk('public')->exists($documento->arquivo)) {
                    Storage::disk('public')->delete($documento->arquivo);
                }

                $file = $request->file('arquivo');
                $documento->arquivo = $file->store('documentos', 'public');
                $documento->tipo    = $file->getClientMimeType();
                $documento->tamanho = $file->getSize();
            }

            $documento->titulo    = $request->titulo    ?? $documento->titulo;
            $documento->categoria = $request->categoria ?? $documento->categoria;
            $documento->descricao = $request->descricao ?? $documento->descricao;
            $documento->save();

            return response()->json($documento->fresh(), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível atualizar o documento'], 500);
        }
    }

    /**
     * Deletar documento
     */
    public function destroy($id): JsonResponse
    {
        try {
            $documento = Documento::find($id);

            if (!$documento) {
                return response()->json(['error' => 'Documento não encontrado'], 404);
            }

            if ($documento->arquivo && Storage::disk('public')->exists($documento->arquivo)) {
                Storage::disk('public')->delete($documento->arquivo);
            }

            $documento->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível excluir o documento'], 500);
        }
    }

    /**
     * Restaurar documento deletado
     */
    public function restore($id): JsonResponse
    {
        try {
            $documento = Documento::withTrashed()->find($id);

            if (!$documento) {
                return response()->json(['error' => 'Documento não encontrado'], 404);
            }

            if (!$documento->trashed()) {
                return response()->json(['error' => 'Documento já está ativo'], 400);
            }

            $documento->restore();

            return response()->json($documento, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível restaurar o documento'], 500);
        }
    }
}