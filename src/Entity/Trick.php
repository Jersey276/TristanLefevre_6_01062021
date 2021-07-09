<?php

namespace App\Entity;

use App\Repository\TrickRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrickRepository::class)
 */
class Trick
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private string $title;

    /**
     * @ORM\Column(type="text")
     */
    private string $description;

    /**
     * @ORM\ManyToOne(targetEntity=TrickGroup::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private TrickGroup $trickGroup;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="Tricks", orphanRemoval=true)
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private Collection $comments;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private \DatetimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private \DatetimeInterface $modifiedAt;

    /**
     * @ORM\OneToMany(targetEntity=Media::class, mappedBy="trick")
     */
    private Collection $medias;

    /**
     * @ORM\OneToOne(targetEntity=Media::class, cascade={"persist", "remove"})
     */
    private ?Media $front;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->medias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTrickGroup(): ?TrickGroup
    {
        return $this->trickGroup;
    }

    public function setTrickGroup(TrickGroup $trickGroup): self
    {
        $this->trickGroup = $trickGroup;

        return $this;
    }

    
    public function getDisplayUrl() : String
    {
        return "/tricks/". $this->getId();
    }

    public function getEditUrl() : String
    {
        return "/tricks/". $this->getId() . "/edit";
    }

    public function getRemoveUrl() : String
    {
        return "/tricks/". $this->getId() . "/remove";
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setTricks($this);
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeInterface $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function isModified(): bool
    {
        return isset($this->modifiedAt);
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedias(): ?Collection
    {
        if (count($this->medias) > 0) {
            return $this->medias;
        }
        return null;
    }

    public function addMedia(Media $media): self
    {
        if (!$this->medias->contains($media)) {
            $this->medias[] = $media;
            $media->setTrick($this);
        }

        return $this;
    }

    public function getFirstMedia(string $type) : ?Media
    {
        $medias = $this->getMedias();
        if ($medias != null) {
            foreach ($medias as $media) {
                if ($media->getType()->getName() == $type) {
                    return $media;
                }
            }
        }
        return null;
    }

    public function getFront(): ?Media
    {
        return $this->front;
    }

    public function getFrontPath(): ?String
    {
        if (isset($this->front)) {
            return $this->front->getPath();
        }
        $media = $this->getFirstMedia('image');
        if ($media != null){
            return $media->getPath();
        }
        return '/images/trick/default.png';
    }

    public function setFront(?Media $front): self
    {
        if (isset($front)) {
            $this->front = $front;
            return $this;
        }
        $this->front = null;
        return $this;
    }
}
