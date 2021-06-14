<?php

namespace App\Entity;

use App\Repository\TrickRepository;
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

    public function setTrickGroup(?TrickGroup $trickGroup): self
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
}
