<?php

namespace App\DataFixtures;

use App\Entity\ServiceCategory;
use App\Facade\Doctrine;

final class CategoryProvider
{
    private const CATEGORIES = [
        'Ногти',
        'Тело',
        'Волосы',
        'Лицо',
        'Пирсинг и тату',
    ];

    private const SUBCATEGORIES = [
        'Ногти' => [
            'Маникюр',
            'Педикюр',
            'Другое',
        ],
        'Тело' => [
            'Эпиляция',
            'Пилинги и обертывания',
            'Аппаратные процедуры',
            'Загар',
        ],
        'Волосы' => [
            'Уход',
            'Стрижка',
            'Окрашивание',
            'Лечение и выпрямление волос',
            'Прически и плетение кос',
            'Наращивание волос',
            'Другое',
        ],
        'Лицо' => [
            'Уход',
            'Макияж',
            'Пермаментный макияж',
            'Ресницы',
            'Брови',
            'Косметология',
            'Другое',
        ],
        'Пирсинг и тату' => [
            'Пирсинг',
            'Тату',
        ],
    ];

    /**
     * @var bool
     */
    private static $newSubcategory = false;

    /**
     * @var int
     */
    private static $calls = 0;

    /**
     * @var null|string
     */
    private static $currentCategory = null;

    /**
     * @param string $categoryName
     *
     * @return int
     */
    public static function parent(string $categoryName): int
    {
        /** @var ServiceCategory $serviceCategory */
        $serviceCategory = Doctrine::getManager()
            ->getRepository(ServiceCategory::class)
            ->findOneBy(['name' => $categoryName]);

        return $serviceCategory->getId();
    }

    /**
     * @param int $category
     *
     * @return string
     */
    public static function category(int $category): string
    {
        return self::CATEGORIES[$category - 1];
    }

    /**
     * @param string $category
     *
     * @return string
     */
    public static function subcategory(string $category): string
    {
        if (!self::$newSubcategory) {
            self::$currentCategory = $category;
            self::$newSubcategory = true;
        }

        if (self::$currentCategory !== $category) {
            self::$calls = 0;
        }

        self::$currentCategory = $category;
        $subcategories = self::SUBCATEGORIES[$category];
        $result = $subcategories[self::$calls];
        self::$calls++;

        return $result;
    }
}
