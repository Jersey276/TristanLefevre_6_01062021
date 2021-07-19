<?php

namespace App\Manager;

use App\Entity\Trick;
use App\Service\FileService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * Manager of Trick entity
 * @author Tristan
 * @version 1
 */
class TrickManager extends AbstractManager
{
    public function __construct(EntityManagerInterface $doctrine, FileService $fileService)
    {
        $this->doctrine = $doctrine;
        $this->fileService = $fileService;
    }

    /**
     * save a new Trick
     * @param Trick $trick new trick
     */
    public function save(Trick $trick) : void
    {
        $trick->setCreatedAt(new DateTime('@'.strtotime('now')));
        $this->doctrine->persist($trick);
        $this->doctrine->flush();
    }

    /**
     * edit a specified Trick
     * @param Trick $trick
     */
    public function edit(Trick $trick) : void
    {
        $trick->setModifiedAt(new DateTime('@'.strtotime('now')));
        $this->doctrine->flush();
    }

    /**
     * remove a specified Trick
     * @param Trick $trick
     */
    public function delete(Trick $trick) : bool
    {
        $this->fileService->remove('/images/trick/'.$trick->getId());
        $this->doctrine->remove($trick);
        try {
            $this->doctrine->flush();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
