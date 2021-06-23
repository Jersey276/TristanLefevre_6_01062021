<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\MediaType;
use App\Entity\Trick;
use App\Manager\MediaManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class MediaController extends AbstractController
{

    /**
     * @Route("/tricks/{id}/media/remove_front/", name="remove_front")
     */
    public function removeFront(Trick $item, MediaManager $manager) : Response
    {
        $manager->removeFront($item);
        return $this->redirectToRoute('tricks_edit_form',['id' => $item->getId()]);
    }

    /**
     * @Route("/tricks/{id}/media/add", name="add_media")
     */
    public function addMedia(Request $request, Trick $item, MediaManager $manager) : Response
    {
        $type = ($this->getDoctrine()
            ->getManager()
            ->find(
                MediaType::class,
                $request->request->get('type')
            ))->getName();
        if ($type == "image") {
            if ($manager->addImage($item, $request->files->get('path'), $request->request->get('type'))) {
                $this->addFlash('notice', 'Media rajouté');
            } else {
                $this->addFlash('danger', 'Problème sur la collecte de l\'image');
            }
        }
        if ($type == "video") {
            if ($manager->addVideo($item, $request->request->get('path'))) {
                $this->addFlash('notice', 'Media rajouté');
            } else {
                $this->addFlash('danger', 'Problème sur l\insetion de la vidéo');
            }
        }
        return $this->redirectToRoute("tricks_edit_form",['id' => $item->getId()]);
    }

    /**
     * @Route("/tricks/{id}/media/modify/{media_id}", name="modify_media")
     * @ParamConverter("media", options={"id" = "media_id"})
     */
    public function ModifyMedia(Request $request, Trick $item, Media $media, MediaManager $manager) : Response
    {
        $type = ($this->getDoctrine()
        ->getManager()
        ->find(
            MediaType::class,
            $request->request->get('type')
        ))->getName();
        if ($type == "image") {
            if ($manager->ChangeImage($item, $media, $request->files->get('path'))) {
                $this->addFlash('notice', 'Media mis à jour');
            } else {
                $this->addFlash('danger', 'Problème avec la mise à jour du média');
            }
        }
        if ($type == "video") {
            if ($manager->changeVideo($media, $request->request->get('path'))) {
                $this->addFlash('notice', 'Media mis à jour');
            } else {
                $this->addFlash('danger', 'Problème avec la mise à jour du média');
            }
        }
        return $this->redirectToRoute("tricks_edit_form",['id' => $item->getId()]);
    }

    /**
     * @Route("/tricks/{id}/media/remove/{media_id}", name="remove_media")
     * @ParamConverter("media", options={"id" = "media_id"})
     */
    public function removeMedia(Trick $item, Media $media, MediaManager $manager) : Response
    {
        if ($manager->removeMedia($media))
        {
            $this->addFlash('notice', 'Media supprimé');
        } else {
            $this->addFlash('danger', 'Problème avec la suppression');
        }
        return $this->redirectToRoute("tricks_edit_form",['id' => $item->getId()]);
    }

    /**
     * @Route("/tricks/{id}/media/set_front/{media_id}", name="media_set_Front")
     * @ParamConverter("media", options={"id" = "media_id"})
     */
    public function setFrontWithMedia(Trick $item, Media $media, MediaManager $manager) : Response
    {
        if ($manager->setFront($item, $media)) {
            $this->addFlash('notice', "L'image de une à été mise à jour");
        } else {
            $this->addFlash('danger', "Problème avec la mise à jour de la une");
        }
        return $this->redirectToRoute("tricks_edit_form",['id' => $item->getId()]);
    }

}
