<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->findBy([],[],5,0);
        return $this->render('index.html.twig', [
            'tricks' => $tricks,
            'offset' => 1,
            'nbToken' => $trickRepository->count([])-5
        ]);
    }
    /**
     * @Route("ask/{base}", name="show_more")
     */
    public function askMore(Request $request, TrickRepository $trickRepository, int $base) : Response
    {
        $tricks = $trickRepository->findBy([],[],5,$base*5);
        foreach ($tricks as $trick) {
            $tricksDisplay[] = $this->renderView('tricks/card.html.twig', ['trick' => $trick]);
        }

        return $this->json([
            'offset' => $base + 1,
            'nbToken' => ($request->query->getInt('nbToken')) - 5,
            'tricks' => $tricksDisplay
        ]);
    }

}
