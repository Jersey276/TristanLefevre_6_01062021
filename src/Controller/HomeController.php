<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller about all function/route about home page and trick listing system
 * @author Tristan
 * @version 1
 */
class HomeController extends AbstractController
{
    const LIMIT = 15;

    /**
     * Display home page with 15 first trick
     * @param TrickRepository $trickRepository repository for trick
     * @return Response Render / Json response
     * @Route("/", name="home")
     */
    public function index(TrickRepository $trickRepository): Response
    {
        $tricks = $trickRepository->findBy([], [], self::LIMIT, 0);
        return $this->render('index.html.twig', [
            'tricks' => $tricks,
            'offset' => 1,
            'nbToken' => $trickRepository->count([])-self::LIMIT
        ]);
    }
    /**
     * Send more trick on trick card template
     * @param Request $request request data
     * @param TrickRepository $trickRepository repository for trick
     * @param int $base offset
     * @return Response Render / Json response
     * @Route("ask/{base}", name="show_more")
     */
    public function askMore(Request $request, TrickRepository $trickRepository, int $base) : Response
    {
        $tricks = $trickRepository->findBy([], [], 5, $base*5);
        $tricksDisplay = array();
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
