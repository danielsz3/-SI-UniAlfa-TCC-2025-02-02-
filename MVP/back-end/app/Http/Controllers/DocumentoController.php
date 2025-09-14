<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentoController extends Controller
{
    // Listar todos
    public function index()
    {
        return response()->json(Documento::all(), 200);
    }

    // Criar novo documento com upload
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'documento' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:2048', // ajuste os mimes/tamanho
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Upload do arquivo
        if ($request->hasFile('documento')) {
            // salva em storage/app/public/documentos
            $path = $request->file('documento')->store('documentos', 'public');
        } else {
            $path = null;
        }

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
        $documento = Documento::findOrFail($id);
        return response()->json($documento, 200);
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

        // Se vier um novo arquivo, apaga o antigo e salva o novo
        if ($request->hasFile('documento')) {
            // apaga antigo se existir
            if ($documento->documento && Storage::disk('public')->exists($documento->documento)) {
                Storage::disk('public')->delete($documento->documento);
            }

            $path = $request->file('documento')->store('documentos', 'public');
            $documento->documento = $path;
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

        // Deletar arquivo do storage
        if ($documento->documento && Storage::disk('public')->exists($documento->documento)) {
            Storage::disk('public')->delete($documento->documento);
        }

        $documento->delete();

        return response()->json(['message' => 'Documento deletado com sucesso'], 200);
    }
}
