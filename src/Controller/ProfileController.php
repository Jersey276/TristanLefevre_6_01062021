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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller about all function/route about user profile system
 * @author Tristan
 * @version 1
 */
class ProfileController extends AbstractController
{
    private TranslatorInterface $translate;

    private UserManager $manager;

    public function __construct(
        TranslatorInterface $translate,
        UserManager $manager
        )
    {
        $this->translate = $translate;
        $this->manager = $manager;
    }

    /**
     * Display profile and manage all profile related forms
     * @param Request $request request data
     * @return Response Render / Json response
     * @Route("/profile", name="profile")
     * @IsGranted("ROLE_USER")
     */
    public function index(Request $request, UserRepository $userRepo): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (($oldUser = $userRepo->find($user->getId())) != null) {
            $oldEmail = $oldUser->getEmail();
        } else {
            $oldEmail = $user->getEmail();
        }

        //password form
        $passwordForm = $this->createForm(ProfilePasswordFormType::class, $user);
        $passwordForm->handleRequest($request);
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $userData = $passwordForm->getData();
            if ($this->manager->changePasswordProfile($userData)) {
                $this->addFlash(
                    'notice',
                    $this->translate->trans('flash.profil.modify.success',['%data%' => 'Le mot de passe','%extend%' => ''])
                );
            } else {
                $this->addFlash(
                    'danger',
                    $this->translate->trans('flash.profil.modify.error',['%data%' => 'Le mot de passe'])
                );
            }
        }

        //email form
        $emailForm = $this->createForm(ProfileEmailFormType::class, $user);
        $emailForm->handleRequest($request);
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            if ($this->manager->changeEmailProfile($user, $oldEmail)) {
                $this->addFlash(
                    'warning',
                    $this->translate->trans('flash.profil.modify.success',[
                        '%data%' => 'L\'adresse mail',
                        '%extend%' => ', vous allez recevoir un mail pour la confirmer'
                    ])
                );
            } else {
                $this->addFlash(
                    'danger',
                    $this->translate->trans('flash.profil.modify.error',['%data%' => 'L\'adresse mail'])
            );
            }
        }

        //avatar form
        $avatarForm = $this->createForm(ProfileAvatarFormType::class);
        $avatarForm->handleRequest($request);
        if ($avatarForm->isSubmitted() && $avatarForm->isValid()) {
            if ($this->manager->changeAvatarProfile($user, $avatarForm->get('avatar')->getData())) {
                $this->addFlash(
                    'notice',
                    $this->translate->trans('flash.profil.modify.success',['%data%' => 'L\'avatar', '%extend%' => ''])
                );
            } else {
                $this->addFlash(
                    'danger',
                    $this->translate->trans('flash.profil.modify.error',['%data%' => 'L\'avatar'])
            );
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
     * @return Response Render / Json response
     * @Route("/profile/remove", name="profile_remove")
     * @IsGranted("ROLE_USER")
     */
    public function remove(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($this->manager->removeUser($user)) {
            $session = new Session();
            $session->invalidate();
            $this->addFlash(
                'notice',
                $this->translate->trans('flash.profil.remove.success')
            );
            return $this->redirectToRoute('app_logout');
        }
        $this->addFlash(
            'danger',
            $this->translate->trans('flash.profil.remove.error')
        );
        return $this->redirectToRoute('profile');
    }
}
