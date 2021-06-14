<?php

namespace App\Manager;

use App\Entity\Trick;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class TrickManager extends AbstractManager
{

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function save(Trick $trick) : void
    {
        $trick->setCreatedAt(new DateTime('@'.strtotime('now')));
        $this->doctrine->persist($trick);
        $this->doctrine->flush();
    }

    public function edit(Trick $trick) : void
    {
        $trick->setModifiedAt(new DateTime('@'.strtotime('now')));
        $this->doctrine->flush();
    }

    public function delete(Trick $trick) : void
    {
        $this->doctrine->remove($trick);
        $this->doctrine->flush();
    }
}