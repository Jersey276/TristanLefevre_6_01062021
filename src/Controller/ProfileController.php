<?php

namespace App\Controller;

use App\Form\ProfileAvatarFormType;
use App\Form\ProfileEmailFormType;
use App\Form\ProfilePasswordFormType;
use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile")
     * @IsGranted("ROLE_USER")
     */
    public function index(Request $request, UserManager $manager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        
        //password form
        $passwordForm = $this->createForm(ProfilePasswordFormType::class, $user);
        $passwordForm->handleRequest($request);
        if($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $userData = $passwordForm->getData();
            if($manager->changePasswordProfile($userData)) {
                $this->addFlash('notice','Le mot de passe à été modifié avec succès');
            } else {
                $this->addFlash('danger','Echec dans la modification du mot de passe');
            }
        }

        //email form
        $emailForm = $this->createForm(ProfileEmailFormType::class, $user);
        $emailForm->handleRequest($request);
        if($emailForm->isSubmitted() && $emailForm->isValid()) {
            $userData = $emailForm->getData();
            if($manager->changeEmailProfile($userData)) {
                $this->addFlash('warning','L\'adresse mail à été modifié avec succès, vous allez recevoir un
                mail pour la confirmer');
            } else {
                $this->addFlash('danger','Echec dans la modification du mot de passe');
            }
        }

        //avatar form
        $avatarForm = $this->createForm(ProfileAvatarFormType::class);
        $avatarForm->handleRequest($request);
        if($avatarForm->isSubmitted() && $avatarForm->isValid()) {
            if($manager->changeAvatarProfile($user, $avatarForm->get('avatar')->getData())) {
                $this->addFlash('notice','L\'avatar à été modifié avec succès');
            } else {
                $this->addFlash('danger','Echec dans la modification de l\'avatar');
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
     * @Route("/profile/remove", name="profile_remove")
     * @IsGranted("ROLE_USER")
     */
    public function remove(UserManager $manager): Response
    {
        if($manager->removeUser($this->getUser())) {
            return $this->redirectToRoute('app_logout');
        }
        $this->addFlash('danger', 'la suppression de votre profil n\'a pu se faire, veuiller ressayer');
        return $this->redirectToRoute('profile');
    }
}
