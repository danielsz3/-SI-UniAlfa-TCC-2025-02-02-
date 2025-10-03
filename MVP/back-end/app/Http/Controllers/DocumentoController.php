<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Traits\SearchIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoController extends Controller
{
    use SearchIndex;

    /**
     * Listar documentos
     */
    public function index(Request $request): JsonResponse
    {
        return $this->SearchIndex(
            $request,
            Documento::query(),
            'documentos',
            ['titulo', 'categoria', 'descricao']
        );
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
            'arquivo'   => 'required|file|mimes:pdf,doc,docx,jpg,png,xls,xlsx,csv|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $file = $request->file('arquivo');
            $path = $file->store('documentos', 'public');

            $documento = Documento::create([
                'titulo'       => $request->titulo,
                'categoria'    => $request->categoria,
                'descricao'    => $request->descricao,
                'arquivo'      => $path,
                'tipo'         => $file->getClientMimeType(),
                'tamanho'      => $file->getSize(),
            ]);

            return response()->json($documento, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
                // $documento->nome_original = $file->getClientOriginalName(); // opcional: requer coluna no banco
                // $documento->url_arquivo = $documento->arquivo; // se quiser manter sincronizado
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

    /**
     * Download do arquivo do documento
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function download($id): StreamedResponse|JsonResponse
    {
        try {
            $documento = Documento::find($id);

            if (!$documento) {
                return response()->json(['error' => 'Documento não encontrado'], 404);
            }

            $path = $documento->arquivo;

            if (!$path || !Storage::disk('public')->exists($path)) {
                return response()->json(['error' => 'Arquivo não encontrado no armazenamento'], 404);
            }

            // Nome do arquivo para download
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $baseName = Str::slug($documento->titulo ?: 'documento');

            // Se houver coluna nome_original no banco, ela será usada; caso contrário, usa título + extensão
            $fileName = !empty($documento->nome_original)
                ? $documento->nome_original
                : ($ext ? "{$baseName}.{$ext}" : $baseName);

            // Content-Type
            $mime = $documento->tipo ?: (Storage::disk('public')->mimeType($path) ?? 'application/octet-stream');

            return Storage::disk('public')->download($path, $fileName, [
                'Content-Type' => $mime,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível realizar o download'], 500);
        }
    }
}
