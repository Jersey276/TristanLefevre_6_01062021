<?php

namespace App\Manager;

use App\Entity\Media;
use App\Entity\MediaType;
use App\Entity\Trick;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Manager of Media entity
 * @author Tristan
 * @version 1
 */
class MediaManager extends AbstractManager
{
    public function __construct(FileService $fileService, EntityManagerInterface $doctrine)
    {
        $this->fileService = $fileService;
        $this->doctrine = $doctrine;
    }

    /**
     * set new Trick front with specified media
     * @param Trick $trick concerned Trick
     * @param Media $media concerned Media
     * @return bool result
     */
    public function setFront(Trick $trick, media $media) : bool
    {
        $media = $this->doctrine->find(Media::class, $media);
        if (isset($media) && $media->getType()->getName() == "image") {
            $trick->setFront($media);
            $this->doctrine->flush();
            return true;
        }
        return false;
    }

    /**
     * remove front from Trick
     * @param Trick $trick concerned Trick
     * @return bool result
     */
    public function removeFront(Trick $trick) : bool
    {
        $trick->setFront(null);
        try {
            $this->doctrine->flush();
            return true;
        } catch (ORMException $e) {
            return false;
        }
    }

    /**
     * Add new image on Trick
     * @param Trick $trick concerned trick
     * @param UploadedFile $file new image for trick
     * @param MediaType $type media type (verification)
     * @return bool result
     */
    public function addImage(Trick $trick, UploadedFile $file, MediaType $type) : bool
    {
        $fileType = $file->getMimeType();
        if ($fileType != null && (explode('/', $fileType))[0] == $type->getName()) {
            //upload file into trick folder
            $filepath = $this->fileService->upload($file, '/images/trick/'.$trick->getId() .'/');

            // create media and fill it
            $media = new Media();
            $media->setPath($filepath);
            $media->setType($type);
            $media->setTrick($trick);
            // send new media info into database
            try {
                $this->doctrine->persist($media);
                $this->doctrine->flush();
                return true;
            } catch (ORMException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Change image of specific media
     * @param Trick $trick concerned Trick
     * @param Media $media concerned Media
     * @param UploadedFile $file uploaded file
     * @return bool result
     */
    public function changeImage(Trick $trick, Media $media, UploadedFile $file) : bool
    {
        if ($media->getType()->getName() == 'image') {
            $this->fileService->remove($media->getPath());
            $media->setPath($this->fileService->upload($file, '/images/trick/'. $trick->getId() . '/'));
        }
        try {
            $this->doctrine->flush();
            return true;
        } catch (ORMException $e) {
            return false;
        }
    }

    /**
     * remove media
     * @param Media $media Concerned Media
     * @return bool result
     */
    public function removeMedia(Media $media) : bool
    {
        try {
            $this->doctrine->remove($media);
            $this->doctrine->flush();
            return true;
        } catch (ORMException $e) {
            return false;
        }
    }

    /**
     * add new Video Media on Trick
     * @param Trick $trick concerned Trick
     * @param String $url video url
     * @return bool result
     */
    public function addVideo(Trick $trick, string $url) : bool
    {
        $media = new Media();
        $mediaType = $this->doctrine->getRepository(MediaType::class)->findOneBy(['name' => 'video']);
        if ($mediaType != null) {
            $media->setType($mediaType);
            $path = $this->generateVideoUrl($url);
            if ($path == null) {
                return false;
            }
            $media->setPath($path);
            $media->setTrick($trick);

            $this->doctrine->persist($media);
            $this->doctrine->flush();
            return true;
        }
        return false;
    }

    /**
     * modify Video Media on Trick
     * @param Media $media concerned Trick
     * @param String $url video url
     * @return bool result
     */
    public function changeVideo(Media $media, string $url) : bool
    {
        $path = $this->generateVideoUrl($url);
        if ($path == null) {
            return false;
        }
        $media->setPath($path);
        try {
            $this->doctrine->flush();
        } catch (ORMException $e) {
            return false;
        }
        return true;
    }

    /**
     * generate Url
     * @param String $url video $url
     * @return string url for media entity
     */
    private function generateVideoUrl(string $url) : ?string
    {
        list(, , $platform, $id) = explode('/', $url);
        switch ($platform) {
            case 'youtu.be':
                return 'youtube/'. $id ;
            case 'dai.ly':
                return 'dailymotion/'.$id;
            default:
                return null;
        }
    }
}
