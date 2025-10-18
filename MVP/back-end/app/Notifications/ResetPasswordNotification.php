<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Redefinição de Senha - PetAffinity')
            ->greeting('Olá, ' . $notifiable->nome . '!')
            ->line('Você está recebendo este email porque recebemos uma solicitação de redefinição de senha para sua conta.')
            ->action('Redefinir Senha', $this->url)
            ->line('Este link de redefinição expira em **60 minutos**.')
            ->line('Se você não solicitou a redefinição de senha, nenhuma ação é necessária e sua senha permanecerá segura.')
            ->salutation('Atenciosamente,')
            ->salutation('Equipe PetAffinity');
    }
}