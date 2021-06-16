<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Manager\MediaManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MediaController extends AbstractController
{

    /**
     * @Route("/tricks/{id}/font/modify", name="change_font")
     */
    public function changeFont(Request $request, Trick $item, MediaManager $manager): Response
    {
        error_log(json_encode($request));
        if($request->files->get('frontName')!= null) {
            $fontFile = $request->files->get('frontName');
            $oldFilePath = $request->request->get('oldName');
            if ($fontFile) {
                $fontPath = $manager->modifyFont($item, $fontFile, $oldFilePath);
                if ($fontPath == false)
                {
                    return $this->json([
                        'errorMessage' => 'mauvais type de fichier'
                    ], 500
                    );
                }
                return $this->json(
                    [
                        'frontPath' => $fontPath
                    ], 200
                );
            }
            return $this->json([
                'errorMessage' => 'fontfile est vide'
            ], 500
            );
        }

        return $this->json([
            'errorMessage' => 'une erreur à eu lieu dans le téléchargement du fichier, veuiller recommencer'
        ], 500
        );
    }

    /**
     * @Route("/tricks/{id}/font/remove", name="remove_font")
     */
    public function removeFont(Request $request, Trick $item, MediaManager $manager) : Response
    {
        $manager->removeFont($item, $request->query->get('filePath'));
        return $this->json(['frontPath' => '/images/trick/default.png'],200);
    }
}
