<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MediaRepository::class)
 */
class Media
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $path;

    /**
     * @ORM\ManyToOne(targetEntity=MediaType::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private MediaType $type;

    /**
     * @ORM\ManyToOne(targetEntity=Trick::class, inversedBy="medias")
     */
    private Trick $trick;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getType(): ?MediaType
    {
        return $this->type;
    }

    public function setType(?MediaType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }

    public function getRemoveUrl() : string
    {
        return '/tricks/'. $this->getTrick()->getId() . '/media/remove/' . $this->getId();
    }

    //functions for Video pnly
    public function getThumbnails() : ?string
    {
        if ($this->getType()->getName() == 'video') {
            list($platform, $id) = explode("/",$this->getPath());
            switch($platform) {
                case 'youtube':
                    return 'https://img.youtube.com/vi/'. $id .'/hqdefault.jpg';
                case 'dailymotion':
                    return 'https://www.dailymotion.com/thumbnail/video/'. $id;
                default :
                    return null;
            }
        }
        return null;
    }

    public function getOriginalUrl() : ?string
    {
        if ($this->getType()->getName() == 'video') {
            list($platform, $id) = explode('/', $this->getPath());   
            switch ($platform) {
                case 'youtube':
                    return 'https://youtu.be/'.$id;
                case 'dailymotion':
                    return 'https://dai.ly/'.$id;
                default :
                    return null;
            }
        }
        return null;
    }

    public function getDisplayUrl() : ?string
    {
        if ($this->getType()->getName() == 'video') {
            list($platform, $id) = explode('/', $this->getPath());   
            switch ($platform) {
                case 'youtube':
                    return 'https://www.youtube.com/embed/'.$id;
                case 'dailymotion':
                    return 'https://www.dailymotion.com/embed/video/'.$id;
                default :
                    return null;
            }
        }
    }

    public function getPlatform() : ?string
    {
        if ($this->getType()->getName() == 'video') {
            list($platform,) = explode('/', $this->getPath());
            return $platform;
        }
        return null;
    }
}
