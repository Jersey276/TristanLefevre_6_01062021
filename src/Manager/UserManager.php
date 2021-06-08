<?php

namespace App\Manager;

use App\Manager\TokenManager;
use App\Entity\Token;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
class UserManager
{
    private ObjectManager $doctrine;
    private MailerInterface $mailer;
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(ObjectManager $doctrine,
        MailerInterface $mailer = null,
        UserPasswordHasherInterface $passwordEncoder = null
    )
    {
        $this->doctrine = $doctrine;
        if (isset($mailer)) {
            $this->mailer = $mailer;
        }
        if (isset($passwordEncoder)) {
            $this->passwordEncoder = $passwordEncoder;
        }
    }

    public function register(User $user)
    {
        // encode password
        $user->setPassword($this->passwordEncoder->hashPassword($user, $user->getPassword()));

        // Set their role
        $user->setRoles(['ROLE_USER']);
        // Save user
        $this->doctrine->persist($user);
        $this->doctrine->flush();

        // Send mail with new token for valid email
        $tm = new TokenManager($this->doctrine);
        $tm->sendToken($user->getUsername(), $this->mailer, "email");
    }

    public function forgotPassword(User $user)
    {
        // Send mail with new token for change password
        $tm = new TokenManager($this->doctrine);
        $tm->sendToken($user->getEmail(), $this->mailer, "password");
    }

    public function changePassword(User $user, String $token)
    {
        // encode password
        $user->setPassword($this->passwordEncoder->hashPassword($user, $user->getPassword()));

        $tm = new TokenManager($this->doctrine);
        $tm->useToken($token, 'changePassword', $user->getPassword());
    }

    public function validEmail(String $token)
    {
        $tm = new TokenManager($this->doctrine);
        return $tm->useToken($token, "email");
    }
}