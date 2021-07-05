<?php

namespace App\Manager;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Manager of Comment entity
 * @author Tristan
 * @version 1
 */
class CommentManager extends AbstractManager
{
    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Save comment on database
     * @param Comment $comment new comment
     * @param Trick $trick Concerned trick
     * @param User $user Author of new comment
     * @return Trick Trick with new comment
     */
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
