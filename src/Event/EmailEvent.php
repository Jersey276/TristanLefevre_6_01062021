<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event for send mail
 * @author Tristan
 * @version 1
 */
class EmailEvent extends Event
{
    /** @var string eventName for send email update */
    const NAME_UPDATE = 'changeEmail.update';
    /** @var string eventName for register validation email */
    const NAME_NEW_ACCOUNT = 'register.newEmail';
    /** @var string eventName for email validation recall*/
    const NAME_RECALL = 'login.emailRecall';
    /** @var string eventName for change password email */
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
