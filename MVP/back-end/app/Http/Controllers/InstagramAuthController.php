<?php

namespace App\Http\Controllers;

use App\Models\Integracao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Para registrar erros

class InstagramAuthController extends Controller
{
    /**
     * Redireciona o usuário para a página de autorização do Instagram.
     */
    public function redirectToInstagram()
    {
        $clientId = env('INSTAGRAM_CLIENT_ID');
        $redirectUri = env('INSTAGRAM_REDIRECT_URI');
        $scope = 'user_profile,user_media';

        // Monta a URL de autorização
        $authUrl = "https://api.instagram.com/oauth/authorize?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'response_type' => 'code',
        ]);

        // Redireciona o usuário para a URL externa
        return redirect()->away($authUrl);
    }

    /**
     * Lida com o callback do Instagram após a autorização.
     */
     public function handleInstagramCallback(Request $request)
    {
        if ($request->has('error')) {
            Log::error('Erro na autorização do Instagram: ' . $request->input('error_description'));
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '?error=auth_failed');
        }

        $code = $request->input('code');

        try {
            $response = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
                'client_id' => env('INSTAGRAM_CLIENT_ID'),
                'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => env('INSTAGRAM_REDIRECT_URI'),
                'code' => $code,
            ]);

            if ($response->failed()) {
                $response->throw();
            }

            $accessToken = $response->json()['access_token'];
            $userId = $response->json()['user_id'];
            
            Integracao::updateOrCreate(
                [
                    // Condições para encontrar o registro
                    'service' => 'instagram',
                    'external_user_id' => $userId, // Usamos o ID do Instagram como chave
                ],
                [
                    // Dados para atualizar ou criar
                    'access_token' => $accessToken,
                ]
            );

            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/dashboard?status=success');

        } catch (\Exception $e) {
            Log::error('Falha ao obter o Access Token do Instagram: ' . $e->getMessage());
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '?error=token_failed');
        }
    }
}