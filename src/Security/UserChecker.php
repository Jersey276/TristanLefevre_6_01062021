<?php


namespace App\Security;

use App\Entity\Token;
use App\Entity\User as AppUser;
use App\Event\EmailEvent;
use App\Manager\TokenManager;
use App\Repository\TokenRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class used for all check before and after authentification
 * @author Tristan
 * @version 1
 */
class UserChecker implements UserCheckerInterface
{
    private EventDispatcherInterface $eventDispatcher;

    private TokenRepository $tokenRepository;

    private TokenManager $tokenManager;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TokenRepository $tokenRepository,
        TokenManager $tokenManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenRepository = $tokenRepository;
        $this->tokenManager = $tokenManager;
    }

    /**
     * All check before Auth
     * @param UserInterface $user
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }
    }

    /**
     * All check after Auth
     * @param UserInterface $user
     */
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        // user email is not verified, the user will be notified
        if (!$user->getIsEmailCheck()) {
            $token = $this->tokenRepository->findOneBy(['user' => $user]);
            if (isset($token)) {
                $this->eventDispatcher->dispatch(new EmailEvent($user->getEmail(), $token->getToken()), EmailEvent::NAME_RECALL);
            } else {
                $token = $this->tokenManager->generateToken($user, 'email');
                $this->eventDispatcher->dispatch(new EmailEvent($user->getEmail(), $token->getToken()), EmailEvent::NAME_RECALL);
            }
            throw new CustomUserMessageAccountStatusException(
                "Votre adresse mail n'a pas été vérifié. <br> Un message de rappel a été envoyé"
            );
        }
    }
}
