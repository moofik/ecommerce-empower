<?php


namespace App\Service\Shop\Maker;


use App\Entity\Shop;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

interface CreationStrategy
{
    /**
     * @param User $user
     * @return Shop
     * @throws ConflictHttpException
     */
    public function create(User $user): Shop;
}