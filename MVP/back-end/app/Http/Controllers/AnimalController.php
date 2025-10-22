<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Endereco;
use App\Models\PreferenciaUsuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Traits\SearchIndex;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class UsuarioController extends Controller
{
    use SearchIndex;

    /*
    |--------------------------------------------------------------------------
    | Listagem
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): JsonResponse
    {
        return $this->SearchIndex(
            $request,
            Usuario::with(['endereco', 'preferencias']),
            'usuarios',
            ['nome', 'email', 'telefone']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Criação
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): JsonResponse
    {
        // Decodifica campos JSON se vierem em formato string no FormData
        if ($request->has('endereco') && is_string($request->input('endereco'))) {
            $request->merge(['endereco' => json_decode($request->input('endereco'), true)]);
        }
        if ($request->has('preferencias') && is_string($request->input('preferencias'))) {
            $request->merge(['preferencias' => json_decode($request->input('preferencias'), true)]);
        }

        // Normaliza data_nascimento para Y-m-d quando vier ISO
        if ($request->filled('data_nascimento') && is_string($request->input('data_nascimento'))) {
            try {
                $dt = Carbon::parse($request->input('data_nascimento'))->startOfDay();
                $request->merge(['data_nascimento' => $dt->toDateString()]);
            } catch (\Throwable $e) {
                // Deixa passar para o validator
            }
        }

        // Validação
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|min:2|max:150',
            'email' => 'required|email|max:150|unique:usuarios,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'
            ],
            'cpf' => 'required|string|size:11|regex:/^[0-9]+$/|unique:usuarios,cpf',
            'data_nascimento' => 'required|date|before:today|after:1900-01-01',
            'telefone' => 'nullable|string|size:11|regex:/^[0-9]+$/',
            'role' => 'nullable|string|in:user,admin',

            // ✅ Validação de imagem idêntica ao AnimalController
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',

            'endereco' => 'nullable|array',
            'endereco.cep' => 'nullable|string|max:9',
            'endereco.logradouro' => 'nullable|string|max:255',
            'endereco.numero' => 'nullable|string|max:10',
            'endereco.complemento' => 'nullable|string|max:100',
            'endereco.bairro' => 'nullable|string|max:100',
            'endereco.cidade' => 'nullable|string|max:100',
            'endereco.uf' => 'nullable|string|max:2',

            'preferencias' => 'nullable|array',
            'preferencias.tamanho_pet' => 'nullable|string|in:pequeno,medio,grande',
            'preferencias.tempo_disponivel' => 'nullable|string|in:pouco_tempo,tempo_moderado,muito_tempo',
            'preferencias.estilo_vida' => 'nullable|string|in:vida_tranquila,ritmo_equilibrado,sempre_em_acao',
            'preferencias.espaco_casa' => 'nullable|string|in:area_pequena,area_media,area_externa',
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter pelo menos 2 caracteres.',
            'nome.max' => 'O nome deve ter no máximo 150 caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.max' => 'O e-mail deve ter no máximo 150 caracteres.',
            'email.unique' => 'Este e-mail já está em uso.',

            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.regex' => 'A senha deve ter no mínimo 8 caracteres, incluir pelo menos 1 letra maiúscula, 1 número e 1 caractere especial.',

            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.size' => 'O CPF deve ter exatamente 11 números.',
            'cpf.regex' => 'O CPF deve conter apenas números.',
            'cpf.unique' => 'Este CPF já está em uso.',

            'data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'data_nascimento.date' => 'A data de nascimento deve ser uma data válida.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'data_nascimento.after' => 'A data de nascimento deve ser posterior a 01/01/1900.',

            'telefone.size' => 'O telefone deve ter exatamente 11 números.',
            'telefone.regex' => 'O telefone deve conter apenas números.',

            'role.in' => 'O papel do usuário deve ser "user" ou "admin".',

            // ✅ Mensagens de validação de imagem
            'imagem.image' => 'O arquivo deve ser uma imagem válida.',
            'imagem.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg ou webp.',
            'imagem.max' => 'A imagem deve ter no máximo 10MB.',

            'endereco.array' => 'O campo endereço deve ser um objeto.',
            'endereco.cep.max' => 'O CEP deve ter no máximo 9 caracteres.',
            'endereco.logradouro.max' => 'O logradouro deve ter no máximo 255 caracteres.',
            'endereco.numero.max' => 'O número deve ter no máximo 10 caracteres.',
            'endereco.complemento.max' => 'O complemento deve ter no máximo 100 caracteres.',
            'endereco.bairro.max' => 'O bairro deve ter no máximo 100 caracteres.',
            'endereco.cidade.max' => 'A cidade deve ter no máximo 100 caracteres.',
            'endereco.uf.max' => 'A UF deve ter no máximo 2 caracteres.',

            'preferencias.array' => 'O campo preferências deve ser um objeto.',
            'preferencias.tamanho_pet.in' => 'O tamanho do pet deve ser pequeno, medio ou grande.',
            'preferencias.tempo_disponivel.in' => 'O tempo disponível deve ser pouco_tempo, tempo_moderado ou muito_tempo.',
            'preferencias.estilo_vida.in' => 'O estilo de vida deve ser vida_tranquila, ritmo_equilibrado ou sempre_em_acao.',
            'preferencias.espaco_casa.in' => 'O espaço da casa deve ser area_pequena, area_media ou area_externa.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                // Upload da imagem (mesmo padrão do AnimalController)
                $pathImagem = null;
                if ($request->hasFile('imagem') && $request->file('imagem')->isValid()) {
                    $file = $request->file('imagem');
                    $nomeOriginal = $file->getClientOriginalName();
                    $path = $file->store('usuarios', 'public');
                    [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];
                    
                    $pathImagem = $path;
                }

                $usuario = Usuario::create([
                    'nome' => $request->nome,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'cpf' => $request->cpf,
                    'data_nascimento' => $request->data_nascimento,
                    'telefone' => $request->telefone,
                    'role' => $request->role ?? 'user',
                    'imagem' => $pathImagem,
                ]);

                // Endereço
                if ($request->has('endereco') && is_array($request->endereco) && !empty(array_filter($request->endereco))) {
                    $dadosEndereco = $request->endereco;
                    $dadosEndereco['id_usuario'] = $usuario->id;
                    Endereco::create($dadosEndereco);
                }

                // Preferências
                if ($request->has('preferencias') && is_array($request->preferencias) && !empty(array_filter($request->preferencias))) {
                    $dadosPref = $request->preferencias;
                    $dadosPref['usuario_id'] = $usuario->id;
                    PreferenciaUsuario::create($dadosPref);
                }

                return response()->json($usuario->load(['endereco', 'preferencias']), 201);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao criar usuário: ' . $e->getMessage(), [
                'exception' => $e,
                'payload' => $request->except('imagem')
            ]);
            return response()->json([
                'error' => 'Não foi possível criar o usuário',
                'message' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Exibição
    |--------------------------------------------------------------------------
    */
    public function show($id): JsonResponse
    {
        try {
            $usuario = Usuario::with(['endereco', 'preferencias'])->find($id);

            if (!$usuario) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            return response()->json($usuario, 200);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir usuário: ' . $e->getMessage(), ['id' => $id, 'exception' => $e]);
            return response()->json(['error' => 'Não foi possível carregar o usuário'], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Atualização (com troca segura de imagem)
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id): JsonResponse
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        // Decodifica JSON
        if ($request->has('endereco') && is_string($request->input('endereco'))) {
            $request->merge(['endereco' => json_decode($request->input('endereco'), true)]);
        }
        if ($request->has('preferencias') && is_string($request->input('preferencias'))) {
            $request->merge(['preferencias' => json_decode($request->input('preferencias'), true)]);
        }

        // Normaliza data_nascimento
        if ($request->filled('data_nascimento') && is_string($request->input('data_nascimento'))) {
            try {
                $dt = Carbon::parse($request->input('data_nascimento'))->startOfDay();
                $request->merge(['data_nascimento' => $dt->toDateString()]);
            } catch (\Throwable $e) {
                // Deixa passar para o validator
            }
        }

        $rules = [
            'nome' => 'sometimes|required|string|min:2|max:150',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:150',
                Rule::unique('usuarios')->ignore($usuario->id)
            ],
            'password' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'
            ],
            'cpf' => [
                'sometimes',
                'required',
                'string',
                'size:11',
                'regex:/^[0-9]+$/',
                Rule::unique('usuarios')->ignore($usuario->id)
            ],
            'data_nascimento' => 'sometimes|required|date|before:today|after:1900-01-01',
            'telefone' => 'nullable|string|size:11|regex:/^[0-9]+$/',
            'role' => 'nullable|string|in:user,admin',

            'endereco' => 'nullable|array',
            'endereco.cep' => 'nullable|string|max:9',
            'endereco.logradouro' => 'nullable|string|max:255',
            'endereco.numero' => 'nullable|string|max:10',
            'endereco.complemento' => 'nullable|string|max:100',
            'endereco.bairro' => 'nullable|string|max:100',
            'endereco.cidade' => 'nullable|string|max:100',
            'endereco.uf' => 'nullable|string|max:2',

            'preferencias' => 'nullable|array',
            'preferencias.tamanho_pet' => 'nullable|string|in:pequeno,medio,grande',
            'preferencias.tempo_disponivel' => 'nullable|string|in:pouco_tempo,tempo_moderado,muito_tempo',
            'preferencias.estilo_vida' => 'nullable|string|in:vida_tranquila,ritmo_equilibrado,sempre_em_acao',
            'preferencias.espaco_casa' => 'nullable|string|in:area_pequena,area_media,area_externa',
        ];

        // ✅ Só valida como file se houver arquivo enviado (igual ao AnimalController)
        if ($request->hasFile('imagem')) {
            $rules['imagem'] = 'file|image|mimes:jpeg,png,jpg,webp|max:10240';
        }

        $validator = Validator::make($request->all(), $rules, [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter pelo menos 2 caracteres.',
            'nome.max' => 'O nome deve ter no máximo 150 caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.max' => 'O e-mail deve ter no máximo 150 caracteres.',
            'email.unique' => 'Este e-mail já está em uso.',

            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.regex' => 'A senha deve ter no mínimo 8 caracteres, incluir pelo menos 1 letra maiúscula, 1 número e 1 caractere especial.',

            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.size' => 'O CPF deve ter exatamente 11 números.',
            'cpf.regex' => 'O CPF deve conter apenas números.',
            'cpf.unique' => 'Este CPF já está em uso.',

            'data_nascimento.date' => 'A data de nascimento deve ser uma data válida.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'data_nascimento.after' => 'A data de nascimento deve ser posterior a 01/01/1900.',

            'telefone.size' => 'O telefone deve ter exatamente 11 números.',
            'telefone.regex' => 'O telefone deve conter apenas números.',

            'role.in' => 'O papel do usuário deve ser "user" ou "admin".',

            // ✅ Mensagens de validação de imagem
            'imagem.image' => 'O arquivo deve ser uma imagem válida.',
            'imagem.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg ou webp.',
            'imagem.max' => 'A imagem deve ter no máximo 10MB.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request, $usuario) {
                $dados = $request->only([
                    'nome',
                    'email',
                    'cpf',
                    'data_nascimento',
                    'telefone',
                    'role'
                ]);

                if ($request->filled('password')) {
                    $dados['password'] = Hash::make($request->password);
                }

                // ✅ Tratamento de imagem — remove antiga se existir
                if ($request->hasFile('imagem')) {
                    if ($usuario->imagem && Storage::disk('public')->exists($usuario->imagem)) {
                        Storage::disk('public')->delete($usuario->imagem);
                    }

                    $file = $request->file('imagem');
                    $nomeOriginal = $file->getClientOriginalName();
                    $path = $file->store('usuarios', 'public');
                    [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];
                    
                    $dados['imagem'] = $path;
                }

                $usuario->update($dados);

                // Atualiza endereço
                if ($request->has('endereco') && is_array($request->endereco) && !empty(array_filter($request->endereco))) {
                    if ($usuario->endereco) {
                        $usuario->endereco->update($request->endereco);
                    } else {
                        $request->endereco['id_usuario'] = $usuario->id;
                        Endereco::create($request->endereco);
                    }
                }

                // Atualiza preferências
                if ($request->has('preferencias') && is_array($request->preferencias) && !empty(array_filter($request->preferencias))) {
                    if ($usuario->preferencias) {
                        $usuario->preferencias->update($request->preferencias);
                    } else {
                        $request->preferencias['usuario_id'] = $usuario->id;
                        PreferenciaUsuario::create($request->preferencias);
                    }
                }

                return response()->json($usuario->fresh(['endereco', 'preferencias']), 200);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage(), [
                'id' => $id,
                'exception' => $e,
                'payload' => $request->except('imagem'),
            ]);
            return response()->json([
                'error' => 'Não foi possível atualizar o usuário',
                'message' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Exclusão (remove imagem também)
    |--------------------------------------------------------------------------
    */
    public function destroy($id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);

            if (!$usuario) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            // ✅ Remove imagem do storage
            if ($usuario->imagem && Storage::disk('public')->exists($usuario->imagem)) {
                Storage::disk('public')->delete($usuario->imagem);
            }

            $usuario->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar usuário: ' . $e->getMessage(), ['id' => $id, 'exception' => $e]);
            return response()->json([
                'error' => 'Não foi possível excluir o usuário',
                'message' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Restauração
    |--------------------------------------------------------------------------
    */
    public function restore($id): JsonResponse
    {
        try {
            $usuario = Usuario::withTrashed()->find($id);

            if (!$usuario) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            if (!$usuario->trashed()) {
                return response()->json(['error' => 'Usuário já está ativo'], 400);
            }

            $usuario->restore();
            return response()->json($usuario->load(['endereco', 'preferencias']), 200);
        } catch (\Exception $e) {
            Log::error('Erro ao restaurar usuário: ' . $e->getMessage(), ['id' => $id, 'exception' => $e]);
            return response()->json([
                'error' => 'Não foi possível restaurar o usuário',
                'message' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }
}