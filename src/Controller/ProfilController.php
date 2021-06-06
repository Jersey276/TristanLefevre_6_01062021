<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    /**
     * @Route("/profil", name="profil")
     */
    public function index(): Response
    {
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }

    /**
     * @Route("/profil", methods={"POST"}, name="modify_profile")
     */
    public function modifyProfil() : Response
    {
        return $this->redirectToRoute('profil',[
            "message" => [
                "type" => "success",
                "text" => ""
            ]
        ]);
    }
}
