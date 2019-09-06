<?php


namespace App\Facade;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordEncoder extends AbstractFacade
{
    /**
     * @return UserPasswordEncoderInterface
     */
    public static function getEncoder(): UserPasswordEncoderInterface
    {
        return static::getContainer()->get('security.password_encoder');
    }
}