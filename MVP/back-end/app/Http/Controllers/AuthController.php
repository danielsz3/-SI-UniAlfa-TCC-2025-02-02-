<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Google\Client as GoogleClient;

class AuthController extends Controller
{
    /**
     * Fazer login normal (email/senha) e gerar JWT
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $credentials = $request->only('email', 'password');

            if (!$token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Credenciais inválidas'], 401);
            }

            return $this->respondWithToken($token);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Erro de autenticação'], 500);
        }
    }

    /**
     * LOGIN COM GOOGLE — redirecionamento (fluxo web)
     */
    public function redirectToGoogle(): JsonResponse
    {
        $googleUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $googleUrl]);
    }

    /**
     * LOGIN COM GOOGLE — callback (fluxo web)
     */
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $usuario = Usuario::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'nome' => $googleUser->getName(),
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'user',
                    'cpf' => '',
                    'data_nascimento' => now()->subYears(18),
                ]
            );

            $token = auth()->login($usuario);

            return response()->json([
                'message' => 'Login com Google efetuado com sucesso!',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
                'user' => $usuario,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro no login com Google', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * LOGIN COM GOOGLE — via ID Token (fluxo de frontend SPA)
     */
    public function googleLoginToken(Request $request): JsonResponse
    {
        $request->validate([
            'idToken' => 'required|string',
        ]);

        $client = new GoogleClient(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $payload = $client->verifyIdToken($request->idToken);

        if (!$payload) {
            return response()->json(['error' => 'Token Google inválido'], 401);
        }

        $email = $payload['email'];
        $nome = $payload['name'] ?? $email;

        $usuario = Usuario::firstOrCreate(
            ['email' => $email],
            [
                'nome' => $nome,
                'password' => Hash::make(Str::random(16)),
                'role' => 'user',
                'cpf' => '',
                'data_nascimento' => now()->subYears(18),
            ]
        );

        $token = auth()->login($usuario);

        return response()->json([
            'message' => 'Login via Google Token efetuado com sucesso!',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => $usuario,
        ]);
    }

    /**
     * Retorna o usuário autenticado
     */
    public function me(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        return response()->json(['message' => 'Usuário autenticado', 'user' => $user]);
    }

    /**
     * Logout e invalida o token
     */
    public function logout(): JsonResponse
    {
        try {
            auth()->logout();
            return response()->json(['message' => 'Logout realizado com sucesso']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token inválido'], 400);
        }
    }

    /**
     * Renovar token JWT
     */
    public function refresh(): JsonResponse
    {
        try {
            return $this->respondWithToken(auth()->refresh());
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        }
    }

    /**
     * Monta resposta padrão do token
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'message' => 'Login realizado com sucesso',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user(),
        ]);
    }

    /**
     * Enviar link de redefinição de senha
     */
    public function forgetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $status = Password::broker('usuarios')->sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Link de redefinição de senha enviado']);
        }

        throw ValidationException::withMessages(['email' => [__($status)]]);
    }

    /**
     * Resetar senha
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $status = Password::broker('usuarios')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Senha redefinida com sucesso']);
        }

        return response()->json(['error' => 'Falha ao redefinir a senha', 'message' => __($status)], 400);
    }
}