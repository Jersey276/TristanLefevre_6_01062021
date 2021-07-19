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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller about all function/route about trick system
 * @author Tristan
 * @version 1
 */
class TrickController extends AbstractController
{
    private TranslatorInterface $translate;

    private TrickManager $trickManager;

    public function __construct(
        TranslatorInterface $translate,
        TrickManager $trickManager
    ) {
        $this->translate = $translate;
        $this->trickManager = $trickManager;
    }

    /**
     * Create a new trick / Display trick form for new Trick
     * @param Request $request request data
     * @return Response Render / Json response
     * @Route("/tricks/new", name="tricks_new_form")
     * @IsGranted("ROLE_USER")
     */
    public function trickNewForm(Request $request) : Response
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

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $this->trickManager->save($tricks);
                    $this->addFlash(
                        'notice',
                        $this->translate->trans('flash.trick.added.success')
                    );
                    return $this->redirectToRoute("tricks_detail", ['title' => $tricks->getTitle()]);
                }
                $this->addFlash(
                    'danger',
                    $this->translate->trans('flash.trick.added.error')
                );
            }
            return $this->render('tricks/tricksForm.html.twig', [
                'form' => $form->createView(),
                'formGroup' => $formGroup->createView(),
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
    public function trickNewCategory(
        Request $request,
        TrickGroupManager $manager
    ) : Response {
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
            'message' => 'Une erreur a eu lieu avec votre formulaire.',
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
     * @Route("/tricks/{title}", name="tricks_detail")
     */
    public function trickDetail(
        Request $request,
        Trick $item,
        CommentManager $manager,
        CommentRepository $commentRepository
    ) : Response {
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
     * @param MediaManager $mediaManager Manager for Media
     * @param MediaRepository $mediaRepository Repository for media
     * @return Response Render / Json response
     * @Route("/tricks/{title}/edit", name="tricks_edit_form")
     * @IsGranted("ROLE_USER")
     */
    public function trickEditForm(
        Request $request,
        Trick $item,
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

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->trickManager->edit($form->getData());
                $this->addFlash(
                    'notice',
                    $this->translate->trans('flash.trick.updated.success')
                );
                return $this->redirectToRoute('tricks_detail', ['title' => $item->getTitle()]);
            }
            $this->addFlash(
                'danger',
                $this->translate->trans('flash.trick.updated.error')
            );
        }

        $formFront = $this->createForm(TrickFrontType::class, $item, ['id' => $item->getId()]);
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
     * Remove trick
     * @param Trick $item concerned trick
     * @return Response Render / Json response
     * @Route("/tricks/{title}/remove", name="tricks_remove")
     * @IsGranted("ROLE_USER")
     */
    public function trickRemove(Trick $item) : Response
    {
        if ($this->trickManager->delete($item))
        {
            $this->addFlash(
                'notice',
                $this->translate->trans('flash.trick.removed.success')
            );
            return $this->redirectToRoute('home');
        }
        $this->addFlash(
            'notice',
            $this->translate->trans('flash.trick.removed.error')
        );
        return $this->redirectToRoute('tricks_detail', ['title' => $item->getTitle()]);
    }
}
