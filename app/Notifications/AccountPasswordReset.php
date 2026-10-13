<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

class AccountPasswordReset extends ResetPassword
{
    protected function resetUrl($notifiable)
    {
        return rtrim(config('app.url'), '/').'/reset-password/'.rawurlencode($this->token).'?'.http_build_query(['email' => $notifiable->getEmailForPasswordReset()]);
    }
}
