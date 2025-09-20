<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DocumentoController extends Controller
{
    /**
     * Listar documentos com paginação
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('_limit', 10);
        $page    = $request->input('_page', 1);

        $documentos = Documento::paginate($perPage, ['*'], 'page', $page);

        $data = $documentos->getCollection()->map(fn($doc) => $this->formatDocumento($doc));

        return response()->json($data)
            ->header('X-Total-Count', $documentos->total())
            ->header('Access-Control-Expose-Headers', 'X-Total-Count');
    }

    /**
     * Criar novo documento com upload
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'titulo'    => 'required|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'documento' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['titulo', 'categoria', 'descricao']);

        if ($request->hasFile('documento')) {
            $file     = $request->file('documento');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs('documentos', $filename, 'public');

            $data['documento']       = $path;
            $data['nome_arquivo']    = $file->getClientOriginalName();
            $data['tamanho_arquivo'] = $file->getSize();
            $data['tipo_arquivo']    = $file->getClientMimeType();
        }

        $documento = Documento::create($data);

        return response()->json(['data' => $this->formatDocumento($documento)], 201);
    }

    /**
     * Mostrar documento
     */
    public function show($id): JsonResponse
    {
        try {
            $documento = Documento::withTrashed()->findOrFail($id);
            return response()->json(['data' => $this->formatDocumento($documento)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Documento não encontrado'], 404);
        }
    }

    /**
     * Atualizar documento
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $documento = Documento::withTrashed()->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'titulo'    => 'sometimes|required|string|max:255',
                'categoria' => 'nullable|string|max:255',
                'descricao' => 'nullable|string',
                'documento' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $documento->fill($request->only(['titulo', 'categoria', 'descricao']));

            // Substituição de arquivo
            if ($request->hasFile('documento')) {
                // Apaga o antigo
                if ($documento->documento && Storage::disk('public')->exists($documento->documento)) {
                    Storage::disk('public')->delete($documento->documento);
                }

                $file     = $request->file('documento');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path     = $file->storeAs('documentos', $filename, 'public');

                $documento->documento       = $path;
                $documento->nome_arquivo    = $file->getClientOriginalName();
                $documento->tamanho_arquivo = $file->getSize();
                $documento->tipo_arquivo    = $file->getClientMimeType();
            }

            $documento->save();

            return response()->json(['data' => $this->formatDocumento($documento)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Documento não encontrado'], 404);
        }
    }

    /**
     * Inativar (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $documento = Documento::findOrFail($id);
            $documento->delete();

            return response()->json(['message' => 'Documento inativado com sucesso']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Documento não encontrado'], 404);
        }
    }

    /**
     * Restaurar documento
     */
    public function restore($id): JsonResponse
    {
        try {
            $documento = Documento::withTrashed()->findOrFail($id);
            $documento->restore();

            return response()->json(['message' => 'Documento restaurado com sucesso']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Documento não encontrado'], 404);
        }
    }

    /**
     * Download do arquivo
     */
    public function download($id)
    {
        try {
            $documento = Documento::findOrFail($id);

            if (!$documento->documento || !Storage::disk('public')->exists($documento->documento)) {
                return response()->json(['error' => 'Arquivo não encontrado'], 404);
            }

            // retorna o download com o nome original do arquivo
            return Storage::disk('public')->download($documento->documento, $documento->nome_arquivo);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Documento não encontrado'], 404);
        }
    }

    /**
     * Formatar saída
     */
    private function formatDocumento($documento): array
    {
        return [
            'id_documento'    => $documento->id_documento,
            'titulo'          => $documento->titulo,
            'categoria'       => $documento->categoria,
            'descricao'       => $documento->descricao,
            'documento'       => $documento->documento,
            'nome_arquivo'    => $documento->nome_arquivo,
            'tamanho_arquivo' => $documento->tamanho_arquivo,
            'tipo_arquivo'    => $documento->tipo_arquivo,
            'url_download'    => $documento->documento ? url('storage/' . $documento->documento) : null,
            'status'          => $documento->deleted_at ? 'inativo' : 'ativo',
            'created_at'      => $documento->created_at,
            'updated_at'      => $documento->updated_at,
            'deleted_at'      => $documento->deleted_at,
        ];
    }
}