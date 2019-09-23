<?php


namespace App\Service\Shop\Maker;


use App\Entity\Shop;
use App\Entity\User;

class ShopMaker
{
    /**
     * @var CreationStrategy
     */
    private $creationStrategy;

    /**
     * ShopMaker constructor.
     * @param CreationStrategy $creationStrategy
     */
    public function __construct(CreationStrategy $creationStrategy)
    {
        $this->creationStrategy = $creationStrategy;
    }

    /**
     * @param User $user
     * @return Shop
     */
    public function create(User $user): Shop
    {
        return $this->creationStrategy->create($user);
    }

    /**
     * @param CreationStrategy $creationStrategy
     */
    public function setStrategy(CreationStrategy $creationStrategy)
    {
        $this->creationStrategy = $creationStrategy;
    }
}