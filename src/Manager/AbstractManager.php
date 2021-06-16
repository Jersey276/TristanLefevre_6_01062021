<?php

namespace App\Manager;

use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractManager
{
    protected EntityManagerInterface $doctrine;

    protected FileService $fileService;
}