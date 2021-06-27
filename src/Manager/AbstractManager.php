<?php

namespace App\Manager;

use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractManager
{
    protected EntityManagerInterface $doctrine;

    protected FileService $fileService;

    protected EventDispatcherInterface $eventDispatcher;
}