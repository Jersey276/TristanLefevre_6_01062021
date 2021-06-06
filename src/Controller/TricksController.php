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
     * @Route("/tricks/new", name="tricks_new_form")
     */
    public function tricksNewForm(array $message = null) : Response
    {
        {
            return $this->render('tricks/tricksForm.html.twig', [
                'isEdit' => false,
                'message' => $message,
            ]);
        }
    }

    /**
     * @Route("/tricks/new", methods={"POST"}, name="tricks_new")
     */
    public function tricksNew() : Response
    {
        return $this->redirectToRoute(
            "tricks_edit_form",
            [
                "id" => 1,
                "message" => [
                    "type" => "success",
                    "message" => "La figure a été ajouté avec succès",
                ],
            ]
        );
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

    /**
     * @Route("/tricks/{id}/edit", name="tricks_edit_form")
     */
    public function tricksEditForm(int $id, array $message = null) : Response
    {
        return $this->render('tricks/tricksForm.html.twig',
        [
            "isEdit" => true,
            "message" => $message,
        ]);
    }

    /**
     * @Route("/tricks/{id}/edit", methods={"POST"}, name="tricks_edit" )
     */
    public function tricksEdit(int $id, array $message = null) : Response
    {
        return $this->redirectToRoute('tricks_edit_form',
        [
            "id" => 1,
            "message" => [
                "type" => "success",
                "message" => "La figure a été modifié avec succès"
            ]
        ]);
    }

    /**
     * @Route("/tricks/{id}/remove", methods={"DELETE"}, name="tricks_remove")
     */
    public function tricksRemove(int $id)
    {
        return $this->redirectToRoute('home');
    }
}
