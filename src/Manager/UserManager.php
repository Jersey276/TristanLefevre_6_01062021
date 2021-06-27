<?php

namespace App\Manager;

use App\Manager\TokenManager;
use App\Entity\User;
use App\Event\EmailEvent;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager extends AbstractManager
{
    private UserPasswordHasherInterface $passwordEncoder;
    private TokenManager $tm;

    public function __construct(
        EntityManagerInterface $doctrine,
        UserPasswordHasherInterface $passwordEncoder,
        TokenManager $tokenManager,
        FileService $fileService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->doctrine = $doctrine;
        $this->passwordEncoder = $passwordEncoder;
        $this->tm = $tokenManager;
        $this->fileService = $fileService;
        $this->eventDispatcher = $eventDispatcher;
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
        $this->tm->sendToken($user->getUsername(), "email");
    }

    public function forgotPassword(User $user) : void
    {
        // Send mail with new token for change password
        $this->tm->sendToken($user->getEmail(), "password");
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