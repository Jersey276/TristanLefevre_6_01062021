<?php

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractManager
{
    protected EntityManagerInterface $doctrine;
}