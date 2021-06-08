<?php

namespace App\Manager;

use App\Entity\Token;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class TokenManager
{
    private ObjectManager $doctrine;

    public function __construct(ObjectManager $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    public function sendToken(String $search, MailerInterface $mailer, String $type)
    {
        $token = new Token();
        $token->setToken();
        $token->setType($type);
        
        switch ($type) {
            case "email":
            $user = $this->doctrine->getRepository(User::class)
                ->findOneBy(['username' => $search]);
                break;
            case "password" :
                $user = $this->doctrine->getRepository(User::class)
                ->findOneBy(['email' => $search]);
                break;
        }

        $token->setIdUser($user->getId());
        $this->doctrine->persist($token);
        $this->doctrine->flush();

        switch ($type) {
            case "email":
                $mail = (new TemplatedEmail())
                ->from($user->getEmail())
                ->to("no-reply@tristan-lefevre.fr")
                ->subject("Confirmer l'adresse mail")
                ->htmlTemplate("email/validEmail.html.twig")
                ->context([
                    'link' => '/verify/email?token='. $token->getToken()
                ]);
                break;
            case "password":
                $mail = (new TemplatedEmail())
                ->from($user->getEmail())
                ->to("no-reply@tristan-lefevre.fr")
                ->subject("Changer le mot de passe")
                ->htmlTemplate("email/changePassword.html.twig")
                ->context([
                    'link' => '/password/change?token='. $token->getToken()
                ]);
                break;
        }
        $mailer->send($mail);
        return true;
    }

    public function useToken(String $token, String $type, String $arg = null)
    {
        $token = $this->doctrine->getRepository(Token::class)
            ->findOneBy(['token' => $token]);
        if (isset($token)) {
            switch ($type) {
            case "email":
                $user = $this->doctrine->getRepository(User::class)
                    ->find($token->getId());
                $user->setIsEmailCheck(true);
                $this->doctrine->flush();
                break;
            case "forgotPassword":
                return true;
                break;
            case "changePassword":
                $user = $this->doctrine->getRepository(User::class)
                    ->find($token->getId());
                $user->setPassword($arg);
                $this->doctrine->flush();
                break;
            default:
                return false;
                break;
            }
        }
        $this->doctrine->remove($token);
        $this->doctrine->flush();
        return true;
    }
}
