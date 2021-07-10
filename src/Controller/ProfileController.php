<?php

namespace App\Controller;

use App\Form\ProfileAvatarFormType;
use App\Form\ProfileEmailFormType;
use App\Form\ProfilePasswordFormType;
use App\Manager\UserManager;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Controller about all function/route about user profile system
 * @author Tristan
 * @version 1
 */
class ProfileController extends AbstractController
{
    /**
     * Display profile and manage all profile related forms
     * @param Request $request request data
     * @param UserManager $manager manager for User entity
     * @return Response Render / Json response
     * @Route("/profile", name="profile")
     * @IsGranted("ROLE_USER")
     */
    public function index(Request $request, UserManager $manager, UserRepository $userRepo): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $oldEmail = ($userRepo->find($user->getId()))->getEmail();
        
        //password form
        $passwordForm = $this->createForm(ProfilePasswordFormType::class, $user);
        $passwordForm->handleRequest($request);
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $userData = $passwordForm->getData();
            if ($manager->changePasswordProfile($userData)) {
                $this->addFlash('notice', 'Le mot de passe à été modifié avec succès');
            } else {
                $this->addFlash('danger', 'Echec dans la modification du mot de passe');
            }
        }

        //email form
        $emailForm = $this->createForm(ProfileEmailFormType::class, $user);
        $emailForm->handleRequest($request);
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            if ($manager->changeEmailProfile($user, $oldEmail)) {
                $this->addFlash('warning', 'L\'adresse mail à été modifié avec succès, vous allez recevoir un
                mail pour la confirmer');
            } else {
                $this->addFlash('danger', 'Echec dans la modification de l\'adresse mail ' . $oldEmail);
            }
        }

        //avatar form
        $avatarForm = $this->createForm(ProfileAvatarFormType::class);
        $avatarForm->handleRequest($request);
        if ($avatarForm->isSubmitted() && $avatarForm->isValid()) {
            if ($manager->changeAvatarProfile($user, $avatarForm->get('avatar')->getData())) {
                $this->addFlash('notice', 'L\'avatar à été modifié avec succès');
            } else {
                $this->addFlash('danger', 'Echec dans la modification de l\'avatar');
            }
        }

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'password_form' => $passwordForm->createView(),
            'email_form' => $emailForm->createView(),
            'avatar_form' => $avatarForm->createView(),
            'user' => $user
        ]);
    }

    /**
     * Remove an user
     * @param UserManager $manager Manager for user entity
     * @return Response Render / Json response
     * @Route("/profile/remove", name="profile_remove")
     * @IsGranted("ROLE_USER")
     */
    public function remove(UserManager $manager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($manager->removeUser($user)) {
            $session = new Session();
            $session->invalidate();
            return $this->redirectToRoute('app_logout');
        }
        $this->addFlash('danger', 'la suppression de votre profil n\'a pu se faire, veuiller ressayer');
        return $this->redirectToRoute('profile');
    }
}
