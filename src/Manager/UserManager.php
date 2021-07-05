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
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function changePasswordProfile(User $user) : bool
    {
        try {
            $user->setPassword($this->passwordEncoder->hashPassword($user, $user->getPassword()));
            $this->doctrine->flush();
        } catch (ORMException $e) {
            return false;
        }
        return true;
    }

    public function changeEmailProfile(User $user) : bool
    {
        try {
            $oldUser = $this->doctrine->getRepository(User::class)->find($user->getId());
            if (!isset($oldUser) || $oldUser->getEmail() == $user->getEmail()) {
                return false;
            }
            $user->setIsEmailCheck(false);
            $this->doctrine->flush();
            $token = $this->tm->generateToken($user, 'email');
            $event = new EmailEvent($user->getEmail(), $token->getToken());
            $this->eventDispatcher->dispatch($event, $event::NAME_UPDATE);
        } catch (ORMException $e) {
            return false;
        }
        return true;
    }

    public function changeAvatarProfile(User $user, UploadedFile $file) : bool
    {
        $user->setAvatar(
            $this->fileService->upload(
                $file,
                '/images/avatar/',
                'avatar'. $user->getUsername()
            )
        );
        try {
            $this->doctrine->flush();
        } catch (ORMException $e) {
            return false;
        }
        return true;
    }

    public function validEmail(String $token) : bool
    {
        return $this->tm->useToken($token, "email");
    }

    public function removeUser(User $user) : bool
    {
        try {
            $this->doctrine->remove($user);
            $this->doctrine->flush();
            return true;
        } catch (ORMException $e) {
            return false;
        }
    }
}
