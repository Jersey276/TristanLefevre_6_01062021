<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Trick;
use App\Manager\MediaManager;
use App\Repository\MediaTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller about all function/route about media system
 * @author Tristan
 * @version 1
 */
class MediaController extends AbstractController
{

    /**
     * @var TranslatorInterface $translate
     */
    private TranslatorInterface $translate;

    /**
     * @var MediaManager $manager
     */
    private MediaManager $manager;

    public function __construct(
        TranslatorInterface $translate,
        MediaManager $manager
        )
    {
        $this->translate = $translate;
        $this->manager = $manager;
    }

    /**
     * Remove selected media for front on trick
     * @param Trick $item concerned trick
     * @return Response Render / Json response
     * @Route("/tricks/{title}/media/remove_front/", name="remove_front")
     * @IsGranted("ROLE_USER")
     */
    public function removeFront(Trick $item) : Response
    {
        $this->manager->removeFront($item);
        return $this->redirectToRoute('tricks_edit_form', ['title' => $item->getTitle()]);
    }

    /**
     * Add media on a trick
     * @param Request $request request data
     * @param Trick $item concerned trick
     * @param MediaTypeRepository $mediaTypeRepo repository for media type
     * @return Response Render / Json response
     * @Route("/tricks/{title}/media/add", name="add_media")
     * @IsGranted("ROLE_USER")
     */
    public function addMedia(
        Request $request,
        Trick $item,
        MediaTypeRepository $mediaTypeRepo
    ) : Response
    {
        $type = ($mediaTypeRepo->find($request->request->getInt('type')));

        if (isset($type)) {
            if ($type->getName() == "image") {
                if ($this->manager->addImage($item, $request->files->get('path'), $type)) {
                    $this->addFlash(
                        'notice', 
                        $this->translate->trans('flash.media.added.success')
                    );
                    return $this->json('Ok',200);
                }
                $this->addFlash(
                    'danger',
                    $this->translate->trans('flash.media.added.error.image')
                );
                return $this->json('error with media upload',200);
            }
            if ($type->getName() == "video") {
                if ($this->manager->addVideo($item, $request->request->filter('path', null, FILTER_SANITIZE_URL))) {
                    $this->addFlash(
                        'notice',
                        $this->translate->trans('flash.media.added.success')
                    );
                    return $this->json('Ok',200);
                }
                $this->addFlash(
                    'danger',
                    $this->translate->trans('flash.media.added.error.video')
                );
                return $this->json('error with media upload',200);
            }
        }
        $this->addFlash(
            'danger',
            'Type de media inconnu'
        );
        return $this->json('UnknowMedia',200);
    }

    /**
     * Add media on a trick
     * @param Request $request request data
     * @param Trick $item concerned trick
     * @param Media $mediaTypeRepo concerned media
     * @param MediaTypeRepository $mediaTypeRepo repository for media type
     * @return Response Render / Json response
     * @Route("/tricks/{title}/media/modify/{media_id}", name="modify_media")
     * @ParamConverter("media", options={"id" = "media_id"})
     * @IsGranted("ROLE_USER")
     */
    public function modifyMedia(
        Request $request,
        Trick $item,
        Media $media,
        MediaTypeRepository $mediaTypeRepo
    ) : Response {
        
        $typeClass = $mediaTypeRepo->find($request->request->get('type'));
        if ($typeClass != null) {
            $type = $typeClass->getName(); 
            if ($type == "image") {
                if ($this->manager->ChangeImage($item, $media, $request->files->get('path'))) {
                    $this->addFlash(
                        'notice',
                        $this->translate->trans('flash.media.added.error.image')
                );
                    return $this->json('Ok', 200);
                }
                $this->addFlash(
                    'danger',
                    $this->translate->trans('flash.media.added.error.image')
                );
                return $this->json('error with media upload', 200);
            }
            if ($type == "video") {
                if ($this->manager->changeVideo($media, $request->request->filter('path', null, FILTER_SANITIZE_URL))) {
                    $this->addFlash(
                        'notice',
                        $this->translate->trans('flash.media.updated.success')
                    );
                    return $this->json('error with media upload', 200);
                }
                $this->addFlash(
                    'danger',
                    $this->translate->trans('flash.media.added.error.video')
                );
            }
            $this->addFlash(
                'danger',
                $this->translate->trans('Type de media inconnu')
            );
            return $this->json('UnknowMedia', 200);
        }
        return $this->json('UnknowMediaType', 200);
    }

    /**
     * Remove media from trick
     * @param Trick $item concerned trick
     * @param Media $media concerned Media
     * @return Response Render / Json response
     * @Route("/tricks/{title}/media/remove/{media_id}", name="remove_media")
     * @ParamConverter("media", options={"id" = "media_id"})
     * @IsGranted("ROLE_USER")
     */
    public function removeMedia(Trick $item, Media $media) : Response
    {
        if ($item->getFront()->getPath() == $media->getPath())
        {
            $this->manager->removeFront($item);
        }
        if ($this->manager->removeMedia($media)) {
            $this->addFlash(
                'notice',
                $this->translate->trans('flash.media.removed.success')
            );
            return $this->redirectToRoute('tricks_edit_form', ['title' => $item->getTitle()]);
        }
            $this->addFlash(
                'danger',
                $this->translate->trans('flash.media.removed.error')
            );
        return $this->json('error with media upload', 500);
    }

    /**
     * Set trick front with specific media
     * @param Trick $item concerned trick
     * @param Media $media concerned media
     * @return Response Render / Json response
     * @Route("/tricks/{title}/media/set_front/{media_id}", name="media_set_Front")
     * @ParamConverter("media", options={"id" = "media_id"})
     * @IsGranted("ROLE_USER")
     */
    public function setFrontWithMedia(Trick $item, Media $media) : Response
    {
        if ($this->manager->setFront($item, $media)) {
            $this->addFlash(
                'notice',
                $this->translate->trans("flash.media.frontset.success")
            );
        } else {
            $this->addFlash(
                'danger',
                $this->translate->trans("flash.media.frontset.error")
            );
        }
        return $this->redirectToRoute("tricks_edit_form", ['title' => $item->getTitle()]);
    }
}
