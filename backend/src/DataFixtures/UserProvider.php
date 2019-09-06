<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Facade\PasswordEncoder;

final class UserProvider
{
    private const CITIES = [
        'Москва',
        'Новосибирск',
        'Казань',
        'Колхозная Ахтуба'
    ];

    private const NAMES = [
        'Евгений',
        'Александр',
        'Иван',
        'Олег'
    ];

    private const LAST_NAMES = [
        'Орловский',
        'Петров',
        'Огурцов',
        'Иванов'
    ];

    private const EMAIL_PARTS = [
        'eugene',
        'mark',
        'spark',
        'clark',
        'lousiane',
        'louis',
        'lewis',
        'myorwn',
        'marvin',
        'apple',
        'cansas',
        'semi',
        'sex',
        'hacker',
        'emma',
        'watson',
        'ilostbutfind',
        'skipme',
        'missme',
        'rather',
        'do',
        'you',
        'think',
        'better',
        'off',
        'alone',
        'telepathye',
        'agency',
        'schizoid',
        'paranoid',
        'melancholia'
    ];

    private const EMAIL_DOMAINS = [
        '@gmail.com',
        '@hotmail.com',
        '@mail.ru',
        '@ya.ru',
        '@inbox.ru',
        '@mail.com',
        '@special.org',
        '@musician.org',
        '@fragile.org',
        '@reality.org',
        '@matrix.org',
        '@cyber.biz',
    ];

    /**
     * @return string
     * @throws \Exception
     */
    public static function username(): string
    {
        return self::EMAIL_PARTS[array_rand(self::EMAIL_PARTS)] . random_int(1, 1000);
    }

    /**
     * @return string
     */
    public static function password(): string
    {
        return PasswordEncoder::getEncoder()->encodePassword(new User(), 'admin');
    }

    /**
     * @return string
     */
    public static function email(): string
    {
        return self::EMAIL_PARTS[array_rand(self::EMAIL_PARTS)]
            . self::EMAIL_PARTS[array_rand(self::EMAIL_PARTS)]
            . self::EMAIL_DOMAINS[array_rand(self::EMAIL_DOMAINS)];
    }

    /**
     * @return string
     */
    public static function city(): string
    {
        return self::CITIES[array_rand(self::CITIES)];
    }

    /**
     * @return string
     */
    public static function first_name(): string
    {
        return self::NAMES[array_rand(self::NAMES)];
    }

    /**
     * @return string
     */
    public static function last_name(): string
    {
        return self::LAST_NAMES[array_rand(self::LAST_NAMES)];
    }
}
