<?php

namespace App\Entity;

use App\Repository\TrickGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrickGroupRepository::class)
 */
class TrickGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private string $nameGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameGroup(): ?string
    {
        return $this->nameGroup;
    }

    public function setNameGroup(string $nameGroup): self
    {
        $this->nameGroup = $nameGroup;

        return $this;
    }

}
