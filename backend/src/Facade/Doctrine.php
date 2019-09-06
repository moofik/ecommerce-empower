<?php

namespace App\Facade;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;

class Doctrine extends AbstractFacade
{
    /**
     * @return Registry
     */
    public static function getRegistry(): Registry
    {
        return static::getContainer()->get('doctrine');
    }

    /**
     * @param string|null $name
     *
     * @return EntityManagerInterface
     */
    public static function getManager($name = null): EntityManagerInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return static::getRegistry()->getManager($name);
    }

    /**
     * @return EntityManagerInterface[]
     */
    public static function getManagers(): array
    {
        return static::getRegistry()->getManagers();
    }
}
