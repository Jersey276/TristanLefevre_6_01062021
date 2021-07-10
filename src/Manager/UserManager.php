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

/**
 * Manager of User entity
 * @author Tristan
 * @version 1
 */
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

    /**
     * register an user
     * @param User $user generated User
     */
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

    /**
     * send token for ask to change password
     * @param String $user concerned user
     */
    public function forgotPassword(String $email) : void
    {
        // Send mail with new token for change password
        $this->tm->sendToken($email, "password");
    }

    /**
     * use token for change password
     * @param User $user concerned user
     */
    public function changePassword(User $user, String $token) : void
    {
        // encode password
        $user->setPassword($this->passwordEncoder->hashPassword($user, $user->getPassword()));

        $this->tm->useToken($token, 'changePassword', $user->getPassword());
    }

    /**
     * change password profile of concerned user
     * @param User $user concerned user
     * @return bool result
     */
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

    /**
     * change email profile of concerned user
     * @param User $user concerned user
     * @return bool result
     */
    public function changeEmailProfile(User $user, String $email) : bool
    {
        try {
            if ($user->getEmail() == $email) {
                return false;
            }
            $user->setIsEmailCheck(false);
            $this->doctrine->flush();
            $token = $this->tm->generateToken($user, 'email');
            $event = new EmailEvent($user->getEmail(), $token->getToken());
            $this->eventDispatcher->dispatch($event, $event::NAME_UPDATE);
            return true;
        } catch (ORMException $e) {
            return false;
        }
    }

    /**
     * change avatar profile of concerned user
     * @param User $user concerned user
     * @param UploadedFile $file uploaded file
     * @return bool result
     */
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

    /**
     * forgot password
     * @param String $token
     * @return bool result
     */
    public function validEmail(String $token) : bool
    {
        return $this->tm->useToken($token, "email");
    }

    /**
     * remove an user
     * @param User $user concerned user
     * @return bool result
     */
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
