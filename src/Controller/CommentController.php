<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller about all function/route about comment system
 * @author Tristan
 * @version 1
 */
class CommentController extends AbstractController
{
    /**
     * @var CommentRepository $repository
     */
    private CommentRepository $repository;

    public function __construct(CommentRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * @var int limit of displayed comment
     */
    const LIMIT = 4 ;
    /**
     * send more comment on comment card template
     * @param Request $request request data
     * @param Trick $item concerned trick
     * @return Response Render / Json response
     * @Route("tricks/{id}/comment", name= "comment")
     */
    public function index(Request $request, Trick $item): Response
    {
        $offset = $request->query->getInt('offset');
        $comments = $this->repository->findBy(['Tricks' => $item->getId()], ['id' => 'DESC'], self::LIMIT, $offset*self::LIMIT);
        $commentDisplay = [];
        foreach ($comments as $comment) {
            $commentDisplay[] = $this->renderView('comment/card.html.twig', ['comment' => $comment]);
        }

        return $this->json([
            'offset' => $offset + 1,
            'nbToken' => ($request->query->getInt('nbToken')) - self::LIMIT,
            'comment' => $commentDisplay
        ]);
    }
}
