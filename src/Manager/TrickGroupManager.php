<?php

namespace App\Manager;

use App\Entity\TrickGroup;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Manager of TrickGroup entity
 * @author Tristan
 * @version 1
 */
class TrickGroupManager extends AbstractManager
{
    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param String $name new TrickGroup name
     * @return TrickGroup Generated TrickGroup
     */
    public function addTrickGroup(String $name) : TrickGroup
    {
        $tricksGroup = new TrickGroup();
        $tricksGroup->setNameGroup($name);
        $this->doctrine->persist($tricksGroup);
        $this->doctrine->flush();

        return $tricksGroup;
    }
}
