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
use App\Manager\MediaManager;
use App\Manager\TrickGroupManager;
use App\Manager\TrickManager;
use App\Repository\CommentRepository;
use App\Repository\MediaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Controller about all function/route about trick system
 * @author Tristan
 * @version 1
 */
class TrickController extends AbstractController
{

    /**
     * Create a new trick / Display trick form for new Trick
     * @param Request $request request data
     * @param TrickManager $manager Manager for trick
     * @return Response Render / Json response
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
                TrickGroupType::class,
                new TrickGroup(),
                [
                    'attr' => [
                        'id' => 'TrickGroupAdd'
                    ]
                ]
            );

            $formFront = $this->createForm(TrickFrontType::class);

            if ($form->isSubmitted() && $form->isValid()) {
                $manager->save($tricks);
                $this->addFlash(
                    'notice',
                    'La figure à été mise à jour'
                );
                return $this->redirectToRoute("tricks_detail", ['id' => $tricks->getId()]);
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
     * Create a new trick group
     * @param Request $request request data
     * @param TrickGroupManager $manager Manager for trick group
     * @return Response Render / Json response
     * @Route("/tricks/new/category", name="add_category")
     * @IsGranted("ROLE_USER")
     */
    public function trickNewCategory(Request $request, TrickGroupManager $manager) : Response
    {
        $name = $request->request->getAlpha('nameGroup');
        if ($name != '') {
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
            ],
            500
        );
    }

    /**
     * Add a comment on trick / Display trick detail template with comment form if user is connected
     * @param Request $request request data
     * @param Trick $item Manager for trick
     * @param CommentManager $manager Manager for Comment
     * @param CommentRepository $commentRepository Repository for comment
     * @return Response Render / Json response
     * @Route("/tricks/{id}", name="tricks_detail")
     */
    public function trickDetail(Request $request, Trick $item, CommentManager $manager, CommentRepository $commentRepository) : Response
    {
        $arg = [ 'tricks' => $item ];
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $comment = new Comment();
            $form = $this->createForm(TrickCommentType::class, $comment);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var \App\Entity\User $user */
                $user = $this->getUser();
                $newTrick = $manager->save($comment, $item, $user);
                $arg['tricks'] = $newTrick;
            }
            $arg['form']= $form->createView();
        }
        $arg['comments'] = $commentRepository->findBy(['Tricks' => $item->getId()], ['id' => 'DESC'], 4);
        $arg['nbToken'] = $commentRepository->count(['Tricks' => $item->getId()]) - 4;
        $arg['offset'] = 1;
        return $this->render('tricks/detail.html.twig', $arg);
    }

    /**
     * Edit a trick / Display trick form for edit Trick
     * @param Request $request request data
     * @param Trick $item concerned trick
     * @param TrickManager $trickManager Manager for trick
     * @param MediaManager $mediaManager Manager for Media
     * @param MediaRepository $mediaRepository Repository for media
     * @return Response Render / Json response
     * @Route("/tricks/{id}/edit", name="tricks_edit_form")
     * @IsGranted("ROLE_USER")
     */
    public function trickEditForm(
        Request $request,
        Trick $item,
        TrickManager $trickManager,
        MediaManager $mediaManager,
        MediaRepository $mediaRepository
    ) : Response {
        $formGroup = $this->createForm(
            TrickGroupType::class,
            new TrickGroup(),
            [
                'attr' => [
                    'id' => 'TrickGroupAdd'
                ]
            ]
        );

        $form = $this->createForm(TrickType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trickManager->edit($form->getData());
            $this->addFlash(
                'notice',
                'La figure à été mise à jour'
            );
            return $this->redirectToRoute('tricks_detail', ['id' => $item->getId()]);
        }

        $formFront = $this->createForm(TrickFrontType::class, $item);
        $formFront->handleRequest($request);

        if ($formFront->isSubmitted() && $formFront->isValid()) {
            $front = $mediaRepository->find($request->request->getInt('front'));
            if ($front != null) {
                $mediaManager->setFront($formFront->getData(), $front);
            }
        }

        return $this->render(
            'tricks/tricksForm.html.twig',
            [
            'form' => $form->createView(),
            'formGroup' => $formGroup->createView(),
            'frontImage' => $formFront->createView(),
            'isEdit' => true,
            'tricks' => $item
        ]
        );
    }

    /**
     * Temove trick
     * @param Trick $item concerned trick
     * @param TrickManager $tricks Manager for trick
     * @return Response Render / Json response
     * @Route("/tricks/{id}/remove", name="tricks_remove")
     * @IsGranted("ROLE_USER")
     */
    public function trickRemove(Trick $item, TrickManager $tricks) : Response
    {
        $tricks->delete($item);
        return $this->redirectToRoute('home');
    }
}
