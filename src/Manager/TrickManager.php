<?php

namespace App\Manager;

use App\Entity\Trick;
use Doctrine\ORM\EntityManagerInterface;

class TrickManager extends AbstractManager
{

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function save(Trick $trick) : void
    {
        $this->doctrine->persist($trick);
        $this->doctrine->flush();
    }

    public function delete(Trick $trick) : void
    {
        $this->doctrine->remove($trick);
        $this->doctrine->flush();
    }
}