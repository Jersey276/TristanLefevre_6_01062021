<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\TrickGroup;
use App\Form\TrickCommentType;
use App\Form\TrickFrontType;
use App\Form\TrickGroupType;
use App\Form\TrickType;
use App\Manager\CommentManager;
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

            $formFront = $this->createForm(TrickFrontType::class);

            if ($form->isSubmitted() && $form->isValid())
            {
                $manager->save($tricks);
                return $this->redirectToRoute("tricks_detail",['id' => $tricks->getId()]);
            }
            return $this->render('tricks/tricksForm.html.twig', [
                'form' => $form->createView(),
                'formGroup' => $formGroup->createView(),
                'frontImage' => $formFront->createView(),
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
    public function trickDetail(Request $request, Trick $item, CommentManager $manager) : Response
    {
        $arg = [ 'tricks' => $item ];
        if ( $this->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            $comment = new Comment();
            $form = $this->createForm(TrickCommentType::class, $comment);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid())
            {
                $newTrick = $manager->save($comment, $item, $this->getUser());
                $arg['tricks'] = $newTrick;
            }
            $arg['form']= $form->createView();
        }
        return $this->render('tricks/detail.html.twig', $arg);
    }

    /**
     * @Route("/tricks/{id}/edit", name="tricks_edit_form")
     * @IsGranted("ROLE_USER")
     */
    public function trickEditForm(Request $request, Trick $item, TrickManager $manager) : Response
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

        $formFront = $this->createForm(TrickFrontType::class, null, [
            'attr' => [
                'id' => 'TrickFrontModify'
            ]
        ]);

        if ($form->isSubmitted() && $form->isValid())
        {
            $manager->edit($item);
            return $this->redirectToRoute('tricks_detail',['id' => $item->getId()]);
        }
        return $this->render('tricks/tricksForm.html.twig',
        [
            'form' => $form->createView(),
            'formGroup' => $formGroup->createView(),
            'frontImage' => $formFront->createView(),
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
