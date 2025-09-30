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
use Illuminate\Validation\Rule;
use App\Traits\SearchIndex;

class UsuarioController extends Controller
{
    use SearchIndex;

    /**
     * Lista de usuários (getList)
     * React Admin espera: { data: [...], total: number }
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

    /**
     * Criar um novo usuário com endereço opcional
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // Validações do usuário
            'nome' => 'required|string|min:2|max:150',
            'email' => 'required|email|max:150|unique:usuarios,email',
            'password' => 'required|min:8|confirmed',
            'cpf' => 'required|string|size:11|regex:/^[0-9]+$/|unique:usuarios,cpf',
            'data_nascimento' => 'required|date|before:today|after:1900-01-01',
            'telefone' => 'nullable|string|size:11|regex:/^[0-9]+$/',
            'role' => 'nullable|string|in:user,admin',
            
            // Validações do endereço (opcionais)
            'endereco.cep' => 'nullable|string|max:9',
            'endereco.logradouro' => 'nullable|string|max:255',
            'endereco.numero' => 'nullable|string|max:10',
            'endereco.complemento' => 'nullable|string|max:100',
            'endereco.bairro' => 'nullable|string|max:100',
            'endereco.cidade' => 'nullable|string|max:100',
            'endereco.uf' => 'nullable|string|max:2',
            
            // Validações das preferências (opcionais)
            'preferencias.tamanho_pet' => 'nullable|string|in:pequeno,medio,grande',
            'preferencias.tempo_disponivel' => 'nullable|string|in:pouco_tempo,tempo_moderado,muito_tempo',
            'preferencias.estilo_vida' => 'nullable|string|in:vida_tranquila,ritmo_equilibrado,sempre_em_acao',
            'preferencias.espaco_casa' => 'nullable|string|in:area_pequena,area_media,area_externa',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                // Criar o usuário
                $usuario = Usuario::create([
                    'nome' => $request->nome,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'cpf' => $request->cpf,
                    'data_nascimento' => $request->data_nascimento,
                    'telefone' => $request->telefone,
                    'role' => $request->role ?? 'user',
                ]);

                // Se foi enviado dados de endereço, criar o endereço
                if ($request->has('endereco') && !empty(array_filter($request->endereco))) {
                    $enderecoData = $request->endereco;
                    $enderecoData['id_usuario'] = $usuario->id;
                    
                    Endereco::create($enderecoData);
                }

                // Se foi enviado dados de preferências, criar as preferências
                if ($request->has('preferencias') && !empty(array_filter($request->preferencias))) {
                    $prefsData = $request->preferencias;
                    $prefsData['usuario_id'] = $usuario->id;
                    
                    PreferenciaUsuario::create($prefsData);
                }

                // Recarregar usuário com endereço e preferências
                $usuario->load(['endereco', 'preferencias']);

                return response()->json($usuario, 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Não foi possível criar o usuário',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibir um usuário específico com endereço
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
            return response()->json(['error' => 'Não foi possível carregar o usuário'], 500);
        }
    }

    /**
     * Atualizar um usuário e seu endereço
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);

            if (!$usuario) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                // Validações do usuário
                'nome' => 'sometimes|required|string|min:2|max:150',
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    'max:150',
                    Rule::unique('usuarios')->ignore($usuario->id)
                ],
                'password' => 'sometimes|required|min:8|confirmed',
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
                
                // Validações do endereço (opcionais)
                'endereco.cep' => 'nullable|string|max:9',
                'endereco.logradouro' => 'nullable|string|max:255',
                'endereco.numero' => 'nullable|string|max:10',
                'endereco.complemento' => 'nullable|string|max:100',
                'endereco.bairro' => 'nullable|string|max:100',
                'endereco.cidade' => 'nullable|string|max:100',
                'endereco.uf' => 'nullable|string|max:2',
                
                // Validações das preferências (opcionais)
                'preferencias.tamanho_pet' => 'nullable|string|in:pequeno,medio,grande',
                'preferencias.tempo_disponivel' => 'nullable|string|in:pouco_tempo,tempo_moderado,muito_tempo',
                'preferencias.estilo_vida' => 'nullable|string|in:vida_tranquila,ritmo_equilibrado,sempre_em_acao',
                'preferencias.espaco_casa' => 'nullable|string|in:area_pequena,area_media,area_externa',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return DB::transaction(function () use ($request, $usuario) {
                // Atualizar dados do usuário
                $userData = $request->only([
                    'nome',
                    'email',
                    'cpf',
                    'data_nascimento',
                    'telefone',
                    'role'
                ]);

                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }

                $usuario->update($userData);

                // Atualizar ou criar endereço se enviado
                if ($request->has('endereco') && !empty(array_filter($request->endereco))) {
                    $enderecoData = $request->endereco;
                    
                    if ($usuario->endereco) {
                        // Atualizar endereço existente
                        $usuario->endereco->update($enderecoData);
                    } else {
                        // Criar novo endereço
                        $enderecoData['id_usuario'] = $usuario->id;
                        Endereco::create($enderecoData);
                    }
                }

                // Atualizar ou criar preferências se enviadas
                if ($request->has('preferencias') && !empty(array_filter($request->preferencias))) {
                    $prefsData = $request->preferencias;
                    
                    if ($usuario->preferencias) {
                        // Atualizar preferências existentes
                        $usuario->preferencias->update($prefsData);
                    } else {
                        // Criar novas preferências
                        $prefsData['usuario_id'] = $usuario->id;
                        PreferenciaUsuario::create($prefsData);
                    }
                }

                return response()->json($usuario->fresh(['endereco', 'preferencias']), 200);
            });
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Não foi possível atualizar o usuário',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar um usuário (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);

            if (!$usuario) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            // Soft delete do usuário (o endereço também será soft deleted se configurado)
            $usuario->delete();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível excluir o usuário'], 500);
        }
    }

    /**
     * Restaurar um usuário deletado
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
            return response()->json(['error' => 'Não foi possível restaurar o usuário'], 500);
        }
    }
}