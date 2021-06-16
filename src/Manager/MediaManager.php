<?php

namespace App\Manager;

use App\Entity\Trick;
use App\Service\FileService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaManager extends AbstractManager
{
    private const DEFAULT_FRONT = '/images/trick/default.png';

    public function __construct(FileService $fileService, EntityManagerInterface $doctrine)
    {
        $this->fileService = $fileService;
        $this->doctrine = $doctrine;
    }

    public function modifyFont(Trick $trick, UploadedFile $file, string $oldFilePath)
    {
        error_log($oldFilePath);
        if (((explode('/',$file->getMimeType()))[0] == 'image' && $this->fileService->remove($oldFilePath)) || $oldFilePath == "")
        {
            $filepath = $this->fileService->upload(
                $file, '/images/trick/'.$trick->getId().'/',
                'defaultFont' . (new DateTime('now'))->getTimestamp()
            );
            $trick->setFrontpath($filepath);
            $this->doctrine->flush();
            return $filepath;
        }
        return false;
    }
    public function removeFont(Trick $trick, string $filepath)
    {
        if ($filepath != self::DEFAULT_FRONT)
        {
            $this->fileService->remove($filepath);
        }
        $trick->setFrontpath(null);
        $this->doctrine->flush();
        return self::DEFAULT_FRONT;
    }
}