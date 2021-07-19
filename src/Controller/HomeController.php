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
    /**
     * @var TrickRepository $repository
     */
    private TrickRepository $repository;

    public function __construct(TrickRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * @var int limit of displayed tricks
     */
    const LIMIT = 15;

    /**
     * Display home page with 15 first trick
     * @return Response Render / Json response
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $tricks = $this->repository->findBy([], [], self::LIMIT, 0);
        return $this->render('index.html.twig', [
            'tricks' => $tricks,
            'offset' => 1,
            'nbToken' => $this->repository->count([])-self::LIMIT
        ]);
    }
    /**
     * Send more trick on trick card template
     * @param Request $request request data
     * @param int $base offset
     * @return Response Render / Json response
     * @Route("ask/{base}", name="show_more")
     */
    public function askMore(Request $request, int $base) : Response
    {
        $tricks = $this->repository->findBy([], [], self::LIMIT, $base*self::LIMIT);
        $tricksDisplay = array();
        foreach ($tricks as $trick) {
            $tricksDisplay[] = $this->renderView('tricks/card.html.twig', ['trick' => $trick]);
        }

        return $this->json([
            'offset' => $base + 1,
            'nbToken' => ($request->query->getInt('nbToken')) - self::LIMIT,
            'tricks' => $tricksDisplay
        ]);
    }
}
