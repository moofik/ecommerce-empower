<?php

namespace App\Service\Shop\Maker;

use App\Entity\Shop;
use App\Entity\User;
use App\Repository\ShopRepository;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class DefaultCreationStrategy implements CreationStrategy
{
    /**
     * @var ShopRepository
     */
    private $shopRepository;

    /**
     * DefaultCreationStrategy constructor.
     *
     * @param ShopRepository $shopRepository
     */
    public function __construct(ShopRepository $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    /**
     * @param User $user
     *
     * @throws ConflictHttpException
     *
     * @return Shop
     */
    public function create(User $user): Shop
    {
        if ($this->shopRepository->findOneBy(['user' => $user, 'name' => $user->getUsername()])) {
            throw new ConflictHttpException('You already have a shop.');
        }

        $shop = (new Shop())
            ->setUser($user)
            ->setName($user->getUsername())
            ->setDescription('Default');

        return $shop;
    }
}
