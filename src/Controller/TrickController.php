<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\TrickGroup;
use App\Form\TrickGroupType;
use App\Form\TrickType;
use App\Manager\TrickGroupManager;
use App\Manager\TrickManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{

    /**
     * @Route("/tricks/{id}", name="tricks_detail")
     */
    public function trickDetail(Trick $item) : Response
    {
        return $this->render('tricks/detail.html.twig', [
            'tricks' => $item
        ]);
    }
}
