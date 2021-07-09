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

/**
 * Controller about all function/route about media system
 * @author Tristan
 * @version 1
 */
class MediaController extends AbstractController
{

    /**
     * Remove selected media for front on trick
     * @param Trick $item concerned trick
     * @param MediaManager $manager manager for media entity
     * @return Response Render / Json response
     * @Route("/tricks/{id}/media/remove_front/", name="remove_front")
     * @IsGranted("ROLE_USER")
     */
    public function removeFront(Trick $item, MediaManager $manager) : Response
    {
        $manager->removeFront($item);
        return $this->redirectToRoute('tricks_edit_form', ['id' => $item->getId()]);
    }

    /**
     * Add media on a trick
     * @param Request $request request data
     * @param Trick $item concerned trick
     * @param MediaTypeRepository $mediaTypeRepo repository for media type
     * @param MediaManager $manager manager for media entity
     * @return Response Render / Json response
     * @Route("/tricks/{id}/media/add", name="add_media")
     * @IsGranted("ROLE_USER")
     */
    public function addMedia(
        Request $request,
        Trick $item,
        MediaTypeRepository $mediaTypeRepo,
        MediaManager $manager
    ) : Response
    {
        $type = ($mediaTypeRepo->find($request->request->getInt('type')));

        if (isset($type)) {
            if ($type->getName() == "image") {
                if ($manager->addImage($item, $request->files->get('path'), $type)) {
                    $this->addFlash('notice', 'Media rajouté');
                } else {
                    $this->addFlash('danger', 'Problème sur la collecte de l\'image');
                }
            }
            if ($type->getName() == "video") {
                if ($manager->addVideo($item, $request->request->filter('path', null, FILTER_SANITIZE_URL))) {
                    $this->addFlash('notice', 'Media rajouté');
                } else {
                    $this->addFlash('danger', 'Problème sur l\insetion de la vidéo');
                }
            }
            return $this->redirectToRoute("tricks_edit_form", ['id' => $item->getId()]);
        }
        $this->addFlash('danger', 'Type de media inconnu');
        return $this->redirectToRoute("tricks_edit_form", ['id' => $item->getId()]);
    }

    /**
     * Add media on a trick
     * @param Request $request request data
     * @param Trick $item concerned trick
     * @param Media $mediaTypeRepo concerned media
     * @param MediaManager $manager manager for media entity
     * @param MediaTypeRepository $mediaTypeRepo repository for media type
     * @return Response Render / Json response
     * @Route("/tricks/{id}/media/modify/{media_id}", name="modify_media")
     * @ParamConverter("media", options={"id" = "media_id"})
     * @IsGranted("ROLE_USER")
     */
    public function modifyMedia(
        Request $request,
        Trick $item,
        Media $media,
        MediaManager $manager,
        MediaTypeRepository $mediaTypeRepo
    ) : Response {
        $type = ($mediaTypeRepo->find($request->request->get('type')));
        if (isset($type)) {
            if ($type == "image") {
                if ($manager->ChangeImage($item, $media, $request->files->get('path'))) {
                    $this->addFlash('notice', 'Media mis à jour');
                } else {
                    $this->addFlash('danger', 'Problème avec la mise à jour du média');
                }
            }
            if ($type == "video") {
                if ($manager->changeVideo($media, $request->request->filter('path', null, FILTER_SANITIZE_URL))) {
                    $this->addFlash('notice', 'Media mis à jour');
                } else {
                    $this->addFlash('danger', 'Problème avec la mise à jour du média');
                }
            }
            return $this->redirectToRoute("tricks_edit_form", ['id' => $item->getId()]);
        }
        $this->addFlash('danger', 'Type de media inconnu');
        return $this->redirectToRoute("tricks_edit_form", ['id' => $item->getId()]);
    }

    /**
     * Remove media from trick
     * @param Trick $item concerned trick
     * @param Media $media concerned Media
     * @param MediaManager $manager manager for media entity
     * @return Response Render / Json response
     * @Route("/tricks/{id}/media/remove/{media_id}", name="remove_media")
     * @ParamConverter("media", options={"id" = "media_id"})
     * @IsGranted("ROLE_USER")
     */
    public function removeMedia(Trick $item, Media $media, MediaManager $manager) : Response
    {
        if ($manager->removeMedia($media)) {
            $this->addFlash('notice', 'Media supprimé');
        } else {
            $this->addFlash('danger', 'Problème avec la suppression');
        }
        return $this->redirectToRoute("tricks_edit_form", ['id' => $item->getId()]);
    }

    /**
     * Set trick front with specific media
     * @param Trick $item concerned trick
     * @param Media $media concerned media
     * @param MediaManager $manager manager for media Entity
     * @return Response Render / Json response
     * @Route("/tricks/{id}/media/set_front/{media_id}", name="media_set_Front")
     * @ParamConverter("media", options={"id" = "media_id"})
     * @IsGranted("ROLE_USER")
     */
    public function setFrontWithMedia(Trick $item, Media $media, MediaManager $manager) : Response
    {
        if ($manager->setFront($item, $media)) {
            $this->addFlash('notice', "L'image de une à été mise à jour");
        } else {
            $this->addFlash('danger', "Problème avec la mise à jour de la une");
        }
        return $this->redirectToRoute("tricks_edit_form", ['id' => $item->getId()]);
    }
}
