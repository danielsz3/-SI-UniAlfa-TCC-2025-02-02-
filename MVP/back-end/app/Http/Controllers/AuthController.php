<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Fazer login e gerar JWT
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:1',
        ], [
            'email.required' => 'O email é obrigatório',
            'email.email' => 'Digite um email válido',
            'password.required' => 'A senha é obrigatória',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $credentials = $request->only('email', 'password');

            if (!$token = auth()->attempt($credentials)) {
                return response()->json([
                    'error' => 'Credenciais inválidas',
                    'message' => 'Email ou senha incorretos'
                ], 401);
            }

            return $this->respondWithToken($token);

        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Erro de autenticação',
                'message' => 'Não foi possível gerar o token'
            ], 500);
        }
    }

    /**
     * Retorna o usuário autenticado
     */
    public function me(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'error' => 'Usuário não autenticado',
                'message' => 'Token inválido ou expirado'
            ], 401);
        }

        return response()->json([
            'message' => 'Usuário autenticado',
            'user' => $user
        ]);
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
            return response()->json([
                'error' => 'Token inválido',
                'message' => 'Não foi possível realizar logout'
            ], 400);
        }
    }

    /**
     * Refresh do token JWT
     */
    public function refresh(): JsonResponse
    {
        try {
            return $this->respondWithToken(auth()->refresh());
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Token inválido',
                'message' => 'Não foi possível renovar o token'
            ], 401);
        }
    }

    /**
     * Estrutura de resposta do token
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'message' => 'Login realizado com sucesso',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

    /**
     * Enviar link de reset de senha
     */
    public function forgetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ], [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $status = Password::broker('usuarios')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Link de redefinição de senha enviado para o email'
            ], 200);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Resetar a senha
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
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'
            ],
            'password_confirmation' => 'required|string|min:8',
        ], [
            'token.required' => 'O token é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'As senhas não coincidem.',
            'password.regex' => 'A senha deve ter no mínimo 8 caracteres, incluir pelo menos 1 letra maiúscula, 1 número e 1 caractere especial.',
            'password_confirmation.required' => 'A confirmação da senha é obrigatória.',
            'password_confirmation.min' => 'A confirmação da senha deve ter no mínimo 8 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $status = Password::broker('usuarios')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Senha redefinida com sucesso'
            ], 200);
        }
        
        return response()->json([
            'error' => 'Não foi possível redefinir a senha',
            'message' => __($status)
        ], 400);    
    }
}