<?php

namespace App\Facade;

use Psr\Container\ContainerInterface;

/**
 * Class AbstractFacade
 * Абстрактный фасад позволяет сделать статическую обертку над контейнером для получения сервисов
 * Нужен для получения сервиса, когда его невозможно передать как зависимость
 */
abstract class AbstractFacade
{
    /**
     * @var ContainerInterface
     */
    private static $container = null;

    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container): void
    {
        if (null !== self::$container) {
            return;
        }
        self::$container = $container;
    }

    /**
     * Для reboot-а в ядре Symfony
     */
    public static function clearContainer(): void
    {
        self::$container = null;
    }

    /**
     * @return ContainerInterface
     */
    protected static function getContainer(): ContainerInterface
    {
        if (null === self::$container) {
            throw new \LogicException("Container wasn't set.");
        }

        return self::$container;
    }
}
