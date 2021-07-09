<?php

namespace App\Manager;

use App\Entity\Token;
use App\Entity\User;
use App\Event\EmailEvent;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manager of Token entity
 * @author Tristan
 * @version 1
 */
class TokenManager extends AbstractManager
{
    public function __construct(EntityManagerInterface $doctrine, EventDispatcherInterface $eventDispatcher)
    {
        $this->doctrine = $doctrine;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * generate token and send it with mail
     * @param String $search email/pseudo for find user
     * @param String $type type of token
     * @return bool result
     */
    public function sendToken(String $search, String $type) : bool
    {
        $token = new Token();
        $token->setToken();
        $token->setType($type);
        
        switch ($type) {
            case "email":
            $user = $this->doctrine->getRepository(User::class)
                ->findOneBy(['username' => $search]);
                break;
            case "password":
                $user = $this->doctrine->getRepository(User::class)
                ->findOneBy(['email' => $search]);
                break;
            default:
            throw new Exception("le type de token demandé n'existe pas");
        }

        if (isset($user)) {
            $token->setIdUser($user->getId());
            $this->doctrine->persist($token);
            $this->doctrine->flush();
            $event = new EmailEvent($user->getEmail(), $token->getToken());
            switch ($type) {
            case "email":
                $this->eventDispatcher->dispatch($event, $event::NAME_NEW_ACCOUNT);
                break;
            case "password":
                $this->eventDispatcher->dispatch($event, $event::NAME_NEW_PWD);
                break;
            default:
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Use generated token
     * @param string $token token from email
     * @param string $type token type
     * @param string|null $arg data arg
     * @return bool result
     */
    public function useToken(String $token, String $type, String $arg = null) : bool
    {
        $token = $this->doctrine->getRepository(Token::class)
            ->findOneBy(['token' => $token]);
        if (isset($token)) {
            switch ($type) {
            case "email":
                $user = $this->doctrine->getRepository(User::class)
                    ->find($token->getIdUser());
                if (!isset($user)) {
                    return false;
                }
                $user->setIsEmailCheck(true);
                $this->doctrine->flush();
                break;
            case "forgotPassword":
                return true;
            case "changePassword":
                $user = $this->doctrine->getRepository(User::class)
                    ->find($token->getIdUser());
                if (!isset($user) || !isset($arg)) {
                    return false;
                }
                $user->setPassword($arg);
                $this->doctrine->flush();
                break;
            default:
                return false;
            }
            $this->doctrine->remove($token);
            $this->doctrine->flush();
            return true;
        }
        return false;
    }

    /**
     * Generate token
     * @param User $user concerned user
     * @param String $type token type
     * @return Token generated token
     */
    public function generateToken(User $user, String $type) : Token
    {
        $token = new Token;
        $token->setToken();
        $token->setType($type);
        $token->setIdUser($user->getId());
        $this->doctrine->persist($token);
        $this->doctrine->flush();
        return $token;
    }
}
