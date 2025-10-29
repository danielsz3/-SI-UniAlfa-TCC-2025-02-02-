<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    /**
     * Token de reset enviado pelo Password Broker
     *
     * @var string
     */
    public $token;

    /**
     * Construtor recebe o token (Laravel passa apenas o token ao chamar sendPasswordResetNotification)
     *
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // FRONTEND_URL em .env (ex: http://localhost:3000)
        $frontend = config('app.frontend_url', env('FRONTEND_URL', env('APP_URL')));
        // Usando /new-password conforme você indicou
        $path = '/new-password';

        // Monta a URL com token e email (email urlencode)
        $url = rtrim($frontend, '/') . $path . '?token=' . $this->token . '&email=' . urlencode($notifiable->email);

        // Tempo de expiração configurado (minutos)
        $expire = config('auth.passwords.usuarios.expire', 60);

        return (new MailMessage)
            ->subject('Redefinição de Senha - PetAffinity')
            ->greeting('Olá, ' . ($notifiable->nome ?? $notifiable->email) . '!')
            ->line('Você está recebendo este e‑mail porque recebemos uma solicitação de redefinição de senha para sua conta.')
            ->action('Redefinir Senha', $url)
            ->line("Este link de redefinição expira em {$expire} minutos.")
            ->line('Se você não solicitou a redefinição de senha, nenhuma ação é necessária e sua senha permanecerá segura.')
            ->salutation('Atenciosamente, Equipe PetAffinity');
    }
}