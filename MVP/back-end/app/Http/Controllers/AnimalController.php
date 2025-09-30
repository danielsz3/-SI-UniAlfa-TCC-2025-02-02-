<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\ImagemAnimal;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
            'ong_id' => 'required|exists:ongs,id',
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

            // imagens (array)
            'imagens' => 'nullable|array',
            'imagens.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()],422);
        }

        return DB::transaction(function () use ($request) {
            $animal = Animal::create($request->only([
                'ong_id','nome','sexo','idade','castrado','vale_castracao',
                'descricao','tipo_animal','nivel_energia','tamanho',
                'tempo_necessario','ambiente_ideal'
            ]));

            if ($request->has('imagens')) {
                foreach ($request->imagens as $img) {
                    ImagemAnimal::create([
                        'animal_id'=>$animal->id,
                        'caminho'=>$img
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

        if ($request->has('imagens')) {
            // limpa antigas e insere novas
            $animal->imagens()->delete();
            foreach ($request->imagens as $img) {
                ImagemAnimal::create([
                    'animal_id'=>$animal->id,
                    'caminho'=>$img
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
