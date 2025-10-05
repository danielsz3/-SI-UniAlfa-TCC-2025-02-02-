<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\ImagemAnimal;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnimalController extends Controller
{
    /**
     * Listar animais
     */
    public function index(): JsonResponse
    {
        $animais = Animal::with(['ong','imagens'])->paginate(10);
        return response()->json($animais);
    }

    /**
     * Criar animal
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_ong' => 'required|exists:ongs,id_ong',
            'nome' => 'required|string|max:100',
            'sexo' => 'required|in:macho,femea',
            'idade' => 'required|integer|min:0',
            'castrado' => 'boolean',
            'vale_castracao' => 'boolean',
            'descricao' => 'nullable|string',
            'tipo_animal' => 'required|in:cao,gato,outro',

            // comportamento
            'nivel_energia' => 'nullable|in:baixa,moderada,alta',
            'tamanho' => 'nullable|in:pequeno,medio,grande',
            'tempo_necessario' => 'nullable|in:pouco_tempo,tempo_moderado,muito_tempo',
            'ambiente_ideal' => 'nullable|in:area_pequena,area_media,area_externa',

            // imagens (upload de arquivos)
            'imagens' => 'nullable|array',
            'imagens.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()],422);
        }

        return DB::transaction(function () use ($request) {
            $animal = Animal::create($request->only([
                'id_ong','nome','sexo','idade','castrado','vale_castracao',
                'descricao','tipo_animal','nivel_energia','tamanho',
                'tempo_necessario','ambiente_ideal'
            ]));

            // Upload de imagens
            if ($request->hasFile('imagens')) {
                foreach ($request->file('imagens') as $file) {
                    $path = $file->store('animais', 'public');
                    [$width, $height] = getimagesize($file->getRealPath()) ?: [null, null];
                    ImagemAnimal::create([
                        'animal_id' => $animal->id,
                        'caminho' => '/storage/' . $path,
                        'width' => $width,
                        'height' => $height,
                    ]);
                }
            }

            return response()->json($animal->load('imagens'),201);
        });
    }

    /**
     * Mostrar um animal
     */
    public function show($id): JsonResponse
    {
        $animal = Animal::with(['ong','imagens'])->find($id);

        if(!$animal){
            return response()->json(['error'=>'Animal não encontrado'],404);
        }

        return response()->json($animal);
    }

    /**
     * Atualizar animal
     */
    public function update(Request $request,$id): JsonResponse
    {
        $animal = Animal::find($id);

        if(!$animal){
            return response()->json(['error'=>'Animal não encontrado'],404);
        }

        $animal->update($request->only([
            'nome','sexo','idade','castrado','vale_castracao',
            'descricao','tipo_animal','nivel_energia','tamanho',
            'tempo_necessario','ambiente_ideal'
        ]));

        // Atualizar imagens se enviadas
        if ($request->hasFile('imagens')) {
            // Remove imagens antigas do storage
            foreach ($animal->imagens as $imagem) {
                $oldPath = str_replace('/storage/', '', $imagem->caminho);
                Storage::disk('public')->delete($oldPath);
            }
            
            // Remove registros antigos do banco
            $animal->imagens()->delete();
            
            // Salva novas imagens
            foreach ($request->file('imagens') as $file) {
                $path = $file->store('animais', 'public');
                [$width, $height] = getimagesize($file->getRealPath()) ?: [null, null];
                ImagemAnimal::create([
                    'animal_id' => $animal->id,
                    'caminho' => '/storage/' . $path,
                    'width' => $width,
                    'height' => $height,
                ]);
            }
        }

        return response()->json($animal->fresh('imagens'));
    }

    /**
     * Deletar (softdelete)
     */
    public function destroy($id): JsonResponse
    {
        $animal = Animal::find($id);

        if(!$animal){
            return response()->json(['error'=>'Animal não encontrado'],404);
        }

        $animal->delete();

        return response()->json(null,204);
    }

    /**
     * Restaurar animal deletado
     */
    public function restore($id): JsonResponse
    {
        $animal = Animal::withTrashed()->find($id);

        if (!$animal) {
            return response()->json(['error' => 'Animal não encontrado'], 404);
        }

        if (!$animal->trashed()) {
            return response()->json(['error' => 'Animal já está ativo'], 400);
        }

        $animal->restore();

        return response()->json($animal->fresh(['ong','imagens']), 200);
    }

    /**
     * Recomendar animais para um usuário de acordo com preferências
     */
    public function recomendar($usuarioId): JsonResponse
    {
        $usuario = Usuario::with('preferencias')->findOrFail($usuarioId);

        if (!$usuario->preferencias) {
            return response()->json([
                'error' => 'Usuário não possui preferências definidas'
            ], 400);
        }

        $animais = Animal::with('imagens')->get();

        $resultados = $animais->map(function ($animal) use ($usuario) {
            $score = 0;

            if ($usuario->preferencias->tamanho_pet === $animal->tamanho) {
                $score += 25;
            }
            if ($usuario->preferencias->tempo_disponivel === $animal->tempo_necessario) {
                $score += 25;
            }
            if ($usuario->preferencias->estilo_vida === $animal->nivel_energia) {
                $score += 25;
            }
            if ($usuario->preferencias->espaco_casa === $animal->ambiente_ideal) {
                $score += 25;
            }

            return [
                'animal' => $animal,
                'afinidade' => $score
            ];
        });

        $ordenados = $resultados->sortByDesc('afinidade')->values();

        return response()->json($ordenados);
    }
}