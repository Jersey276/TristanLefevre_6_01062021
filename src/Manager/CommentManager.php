<?php

namespace App\Manager;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class CommentManager extends AbstractManager
{
    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    public function save(Comment $comment, Trick $trick, User $user) : Trick
    {
        $comment->setUser($user);
        $comment->setTricks($trick);
        $comment->setCreatedAt(new DateTime('@'.strtotime('now')));
        $this->doctrine->persist($comment);
        $this->doctrine->flush();
        $trick->addComment($comment);
        return $trick;
    }
}
