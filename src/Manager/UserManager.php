<?php

namespace App\Manager;

use App\Manager\TokenManager;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager extends AbstractManager
{

    private MailerInterface $mailer;
    private UserPasswordHasherInterface $passwordEncoder;
    private TokenManager $tm;

    public function __construct(EntityManagerInterface $doctrine,
        MailerInterface $mailer,
        UserPasswordHasherInterface $passwordEncoder,
        TokenManager $tokenManager
    )
    {
        $this->doctrine = $doctrine;
        $this->mailer = $mailer;
        $this->passwordEncoder = $passwordEncoder;
        $this->tm = $tokenManager;
    }

    public function register(User $user) : void
    {
        // encode password
        $user->setPassword($this->passwordEncoder->hashPassword($user, $user->getPassword()));

        // Set their role
        $user->setRoles(['ROLE_USER']);
        // Save user
        $this->doctrine->persist($user);
        $this->doctrine->flush();

        // Send mail with new token for valid email
        $this->tm->sendToken($user->getUsername(), $this->mailer, "email");
    }

    public function forgotPassword(User $user) : void
    {
        // Send mail with new token for change password
        $this->tm->sendToken($user->getEmail(), $this->mailer, "password");
    }

    public function changePassword(User $user, String $token) : void
    {
        // encode password
        $user->setPassword($this->passwordEncoder->hashPassword($user, $user->getPassword()));

        $this->tm->useToken($token, 'changePassword', $user->getPassword());
    }

    public function validEmail(String $token) : bool
    {
        return $this->tm->useToken($token, "email");
    }
}