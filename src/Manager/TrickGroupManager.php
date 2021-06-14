<?php

namespace App\Manager;

use App\Entity\TrickGroup;
use Doctrine\ORM\EntityManagerInterface;

class TrickGroupManager Extends AbstractManager
{

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;    
    }

    public function addTrickGroup(String $name) : TrickGroup
    {
        $tricksGroup = new TrickGroup();
        $tricksGroup->setNameGroup($name);
        $this->doctrine->persist($tricksGroup);
        $this->doctrine->flush();

        return $this->doctrine->getRepository(TrickGroup::class)->findOneBy(['nameGroup'=>$name]);
    }
}