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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class TrickController extends AbstractController
{

    /**
     * @Route("/tricks/new", name="tricks_new_form")
     * @IsGranted("ROLE_USER")
     */
    public function trickNewForm(Request $request, TrickManager $manager) : Response
    {
        {
            $tricks = new Trick();
            $form = $this->createForm(TrickType::class, $tricks);
            $form->handleRequest($request);

            $formGroup = $this->createForm(
                TrickGroupType::class, new TrickGroup(),[
                    'attr' => [
                        'id' => 'TrickGroupAdd'
                    ]
                ]
            );

            if ($form->isSubmitted() && $form->isValid())
            {
                $manager->save($tricks);
                return $this->redirectToRoute("tricks_detail",['id' => $tricks->getId()]);
            }
            return $this->render('tricks/tricksForm.html.twig', [
                'form' => $form->createView(),
                'formGroup' => $formGroup->createView(),
                'isEdit' => false,
            ]);
        }
    }

    /**
     * @Route("/tricks/new/category", name="add_category")
     * @IsGranted("ROLE_USER")
     */
    public function trickNewCategory(Request $request, TrickGroupManager $manager) : Response
    {
        $name = $request->request->get('nameGroup');
        if (isset($name)) {
            $tricksGroup = $manager->addTrickGroup($name);

            return $this->json(
                [
                'id' => $tricksGroup->getId(),
                'nameGroup' => $tricksGroup->getNameGroup()
            ]
            );
        }
        return $this->json(
            [
            'message' => 'Une erreur a eu lieu avec votre formulaire',
            ], 500
        );
    }

    /**
     * @Route("/tricks/{id}", name="tricks_detail")
     */
    public function trickDetail(Trick $item) : Response
    {
        return $this->render('tricks/detail.html.twig', [
            'tricks' => $item
        ]);
    }

    /**
     * @Route("/tricks/{id}/edit", name="tricks_edit_form")
     * @IsGranted("ROLE_USER")
     */
    public function trickEditForm(Request $request, Trick $item) : Response
    {
        $formGroup = $this->createForm(
            TrickGroupType::class, new TrickGroup(),[
                'attr' => [
                    'id' => 'TrickGroupAdd'
                ]
            ]
        );

        $form = $this->createForm(TrickType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('tricks_detail',['id' => $item->getId()]);
        }
        return $this->render('tricks/tricksForm.html.twig',
        [
            'form' => $form->createView(),
            'formGroup' => $formGroup->createView(),
            'isEdit' => true,
            'tricks' => $item
        ]);
    }

    /**
     * @Route("/tricks/{id}/remove", name="tricks_remove")
     * @IsGranted("ROLE_USER")
     */
    public function trickRemove(Trick $item, TrickManager $tricks) : Response
    {
        $tricks->delete($item);
        return $this->redirectToRoute('home');
    }
}
