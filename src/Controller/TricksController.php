<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TricksController extends AbstractController
{
    /**
     * @Route("/tricks", name="tricks")
     */
    public function index(): Response
    {
        return $this->render('tricks/index.html.twig', [
            'controller_name' => 'TricksController',
        ]);
    }

    /**
     * @Route("/tricks/{id}", name="tricks_detail")
     */
    public function tricksDetail(int $id) : Response
    {
        return $this->render('tricks/detail.html.twig', [
            'id' => $id,
        ]);
    }
}
