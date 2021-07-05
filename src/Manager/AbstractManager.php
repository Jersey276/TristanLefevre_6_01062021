<?php

namespace App\Manager;

use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractManager
{
    /** @var EntityManagerInterface doctrine Manager for get objects from database*/
    protected EntityManagerInterface $doctrine;

    /** @var FileService service for manage files */
    protected FileService $fileService;

    /** @var EventDispatcherInterface event dispatcher system */
    protected EventDispatcherInterface $eventDispatcher;
}
