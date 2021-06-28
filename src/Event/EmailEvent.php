<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class EmailEvent extends Event
{
    const NAME_UPDATE = 'changeEmail.update';
    const NAME_NEW_ACCOUNT = 'register.newEmail';
    const NAME_RECALL = 'login.emailRecall';
    const NAME_NEW_PWD = 'login.changePassword';

    private string $email;
    private string $token;

    public function __construct(string $email, string $token)
    {
        $this->email = $email;
        $this->token = $token;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function getToken() : string
    {
        return $this->token;
    }
    
}