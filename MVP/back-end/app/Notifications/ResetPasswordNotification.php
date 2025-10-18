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
            ->subject('Redefinir Senha')
            ->line('Você está recebendo este email porque recebemos uma solicitação de redefinição de senha.')
            ->action('Redefinir Senha', $this->url)
            ->line('Este link expira em 60 minutos.')
            ->line('Se você não solicitou, ignore este email.');
    }
}