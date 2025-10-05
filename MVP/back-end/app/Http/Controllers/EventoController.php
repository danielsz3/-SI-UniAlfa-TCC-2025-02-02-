<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\ImagemEvento;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EventoController extends Controller
{
    public function index(): JsonResponse
    {
        $eventos = Evento::with('imagens')->paginate(10);
        return response()->json($eventos);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'local' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'imagem_capa' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'imagens' => 'nullable|array',
            'imagens.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            $data = $request->only(['titulo', 'data_inicio', 'data_fim', 'local', 'descricao']);

            // Upload imagem capa
            if ($request->hasFile('imagem_capa')) {
                $path = $request->file('imagem_capa')->store('eventos/capa', 'public');
                $data['imagem_capa'] = '/storage/' . $path;
            }

            $evento = Evento::create($data);

            // Upload imagens adicionais
            if ($request->hasFile('imagens')) {
                foreach ($request->file('imagens') as $file) {
                    $path = $file->store('eventos', 'public');
                    [$width, $height] = getimagesize($file->getRealPath()) ?: [null, null];
                    ImagemEvento::create([
                        'evento_id' => $evento->id,
                        'caminho' => '/storage/' . $path,
                        'width' => $width,
                        'height' => $height,
                    ]);
                }
            }

            return response()->json($evento->load('imagens'), 201);
        });
    }

    public function show($id): JsonResponse
    {
        $evento = Evento::with('imagens')->find($id);

        if (!$evento) {
            return response()->json(['error' => 'Evento não encontrado'], 404);
        }

        return response()->json($evento);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json(['error' => 'Evento não encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'sometimes|required|string|max:255',
            'data_inicio' => 'sometimes|required|date',
            'data_fim' => 'sometimes|required|date|after_or_equal:data_inicio',
            'local' => 'sometimes|required|string|max:255',
            'descricao' => 'nullable|string',
            'imagem_capa' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'imagens' => 'nullable|array',
            'imagens.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request, $evento) {
            $data = $request->only(['titulo', 'data_inicio', 'data_fim', 'local', 'descricao']);

            // Atualizar imagem capa
            if ($request->hasFile('imagem_capa')) {
                // Deletar imagem capa antiga
                if ($evento->imagem_capa) {
                    $oldPath = str_replace('/storage/', '', $evento->imagem_capa);
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $request->file('imagem_capa')->store('eventos/capa', 'public');
                $data['imagem_capa'] = '/storage/' . $path;
            }

            $evento->update($data);

            // Atualizar imagens adicionais
            if ($request->hasFile('imagens')) {
                // Deletar imagens antigas
                foreach ($evento->imagens as $imagem) {
                    $oldPath = str_replace('/storage/', '', $imagem->caminho);
                    Storage::disk('public')->delete($oldPath);
                }
                $evento->imagens()->delete();

                // Salvar novas imagens
                foreach ($request->file('imagens') as $file) {
                    $path = $file->store('eventos', 'public');
                    [$width, $height] = getimagesize($file->getRealPath()) ?: [null, null];
                    ImagemEvento::create([
                        'evento_id' => $evento->id,
                        'caminho' => '/storage/' . $path,
                        'width' => $width,
                        'height' => $height,
                    ]);
                }
            }

            return response()->json($evento->fresh('imagens'));
        });
    }

    public function destroy($id): JsonResponse
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json(['error' => 'Evento não encontrado'], 404);
        }

        $evento->delete();

        return response()->json(null, 204);
    }

    public function restore($id): JsonResponse
    {
        $evento = Evento::withTrashed()->find($id);

        if (!$evento) {
            return response()->json(['error' => 'Evento não encontrado'], 404);
        }

        if (!$evento->trashed()) {
            return response()->json(['error' => 'Evento já está ativo'], 400);
        }

        $evento->restore();

        return response()->json($evento->fresh('imagens'), 200);
    }
}