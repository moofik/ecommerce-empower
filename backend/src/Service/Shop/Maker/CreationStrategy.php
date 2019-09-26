<?php

namespace App\Service\Shop\Maker;

use App\Entity\Shop;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

interface CreationStrategy
{
    /**
     * @param User $user
     *
     * @throws ConflictHttpException
     *
     * @return Shop
     */
    public function create(User $user): Shop;
}
