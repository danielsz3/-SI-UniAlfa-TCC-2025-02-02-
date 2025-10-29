<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\ImagemPost;
use App\Models\Integracao;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = Post::with('imagens')->paginate(10);
        return response()->json($posts);
    }

    public function store(Request $request): JsonResponse
    {
        Log::info('Upload debug', ['request' => $request->all()]);

        // 🔹 Validação inicial
        $validator = Validator::make($request->all(), [
            'legenda' => 'nullable|string|max:1000',
            'imagens' => 'nullable|array',
            'imagens.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'imagens.*.image' => 'Cada arquivo deve ser uma imagem válida.',
            'imagens.*.mimes' => 'As imagens devem ser do tipo jpeg, png, jpg ou webp.',
            'imagens.*.max' => 'Cada imagem deve ter no máximo 2MB.',
            'legenda.max' => 'A legenda deve ter no máximo 1000 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 🔹 Transação principal
        return DB::transaction(function () use ($request) {
            $post = Post::create($request->only('legenda'));

            $imagens = [];

            // 🔹 Upload e validação das imagens
            if ($request->hasFile('imagens')) {
                foreach ($request->file('imagens') as $index => $file) {
                    $path = $file->store('posts', 'public');
                    $nomeOriginal = $file->getClientOriginalName();

                    try {
                        [$width, $height] = getimagesize(storage_path("app/public/{$path}")) ?: [null, null];
                    } catch (\Throwable $e) {
                        return response()->json([
                            'errors' => ["imagens.$index" => "Não foi possível ler a imagem {$nomeOriginal}."]
                        ], 422);
                    }

                    if (!$width || !$height) {
                        Storage::disk('public')->delete($path);
                        return response()->json([
                            'errors' => ["imagens.$index" => "A imagem {$nomeOriginal} está corrompida ou ilegível."]
                        ], 422);
                    }

                    $ratio = round($width / $height, 2);
                    $portraitRatio = 0.8;  // 4:5
                    $landscapeRatio = 1.91; // 1.91:1
                    $tolerance = 0.02;

                    $isPortrait = abs($ratio - $portraitRatio) <= $tolerance;
                    $isLandscape = abs($ratio - $landscapeRatio) <= $tolerance;

                    if (!$isPortrait && !$isLandscape) {
                        Storage::disk('public')->delete($path);
                        return response()->json([
                            'errors' => [
                                "imagens.$index" =>
                                "A imagem {$nomeOriginal} tem proporção inválida ({$ratio}:1). Use 4:5 (retrato) ou 1.91:1 (paisagem)."
                            ]
                        ], 422);
                    }

                    $imagem = ImagemPost::create([
                        'post_id' => $post->id,
                        'caminho' => $path,
                        'nome_original' => $nomeOriginal,
                        'width' => $width,
                        'height' => $height,
                    ]);

                    $imagens[] = [
                        'path' => $path,
                        'nome_original' => $nomeOriginal,
                    ];
                }
            }

            // 🔹 Integração com Instagram
            $integracao = Integracao::where('service', 'instagram')->first();

            if (!$integracao) {
                return response()->json([
                    'error' => 'Nenhuma integração com o serviço Instagram encontrada.'
                ], 500);
            }

            // 🔹 Montagem do multipart
            $multipart = [
                [
                    'name' => 'legenda',
                    'contents' => $post->legenda ?? '',
                ],
            ];

            foreach ($imagens as $index => $img) {
                $filePath = storage_path("app/public/{$img['path']}");
                if (file_exists($filePath)) {
                    $multipart[] = [
                        'name' => "imagens[$index]",
                        'contents' => fopen($filePath, 'r'),
                        'filename' => $img['nome_original'],
                    ];
                } else {
                    Log::warning("Arquivo não encontrado para envio ao n8n: {$filePath}");
                }
            }

            foreach ($integracao->getAttributes() as $key => $value) {
                $multipart[] = [
                    'name' => "integracao[$key]",
                    'contents' => (string) $value,
                ];
            }

            // 🔹 Envio ao n8n
            try {
                $response = Http::asMultipart()
                    ->withOptions(['verify' => false]) // ⚠️ apenas para dev
                    ->post('https://n8n.chatfacil.cloud/webhook-test/postar-instagram', $multipart);

                if (!$response->successful()) {
                    Log::error("Erro n8n: " . $response->body());
                    throw new \Exception("Erro ao enviar para n8n: " . $response->body());
                }

                Log::info('Envio ao n8n bem-sucedido', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Erro ao enviar post para n8n: ' . $e->getMessage());
            }

            return response()->json($post->load('imagens'), 201);
        });
    }



    public function show($id): JsonResponse
    {
        $post = Post::with('imagens')->find($id);

        if (!$post) {
            return response()->json(['error' => 'Post não encontrado'], 404);
        }

        return response()->json($post);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['error' => 'Post não encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'legenda' => 'nullable|string|max:1000',
            'imagens' => 'nullable|array',
            'imagens.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'imagens.*.image' => 'Cada arquivo deve ser uma imagem válida.',
            'imagens.*.mimes' => 'As imagens devem ser do tipo jpeg, png, jpg ou webp.',
            'imagens.*.max' => 'Cada imagem deve ter no máximo 2MB.',
            'legenda.max' => 'A legenda deve ter no máximo 1000 caracteres.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (empty($request->legenda) && !$request->hasFile('imagens')) {
                $validator->errors()->add('legenda', 'Você deve enviar uma legenda ou pelo menos uma imagem.');
                $validator->errors()->add('imagens', 'Você deve enviar uma legenda ou pelo menos uma imagem.');
            }
        });

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request, $post) {
            $post->update($request->only('legenda'));

            if ($request->hasFile('imagens')) {
                // Deletar imagens antigas
                foreach ($post->imagens as $imagem) {
                    $oldPath = str_replace('/storage/', '', $imagem->caminho);
                    Storage::disk('public')->delete($oldPath);
                }
                $post->imagens()->delete();

                // Salvar novas imagens
                foreach ($request->file('imagens') as $file) {
                    $nomeOriginal = $file->getClientOriginalName(); // 🔹 ADICIONADO
                    $path = $file->store('posts', 'public');
                    [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];
                    ImagemPost::create([
                        'post_id' => $post->id,
                        'caminho' => $path,
                        'nome_original' => $nomeOriginal, // 🔹 ADICIONADO
                        'width' => $width,
                        'height' => $height,
                    ]);
                }
            }

            return response()->json($post->fresh('imagens'));
        });
    }

    public function destroy($id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['error' => 'Post não encontrado'], 404);
        }

        $post->delete();

        return response()->json(null, 204);
    }

    public function restore($id): JsonResponse
    {
        $post = Post::withTrashed()->find($id);

        if (!$post) {
            return response()->json(['error' => 'Post não encontrado'], 404);
        }

        if (!$post->trashed()) {
            return response()->json(['error' => 'Post já está ativo'], 400);
        }

        $post->restore();

        return response()->json($post->fresh('imagens'), 200);
    }
}
