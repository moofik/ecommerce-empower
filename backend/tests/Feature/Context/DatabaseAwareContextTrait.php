<?php


namespace App\Tests\Feature\Context;


use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

trait DatabaseAwareContextTrait
{
    private $entityManager;

    /**
     * Purges database
     *
     * @BeforeScenario @purgeDatabase
     */
    public function purgeDatabase()
    {
        if (!$this->entityManager instanceof EntityManagerInterface) {
            throw new \InvalidArgumentException(__CLASS__.' should provide entityManager property implementing '.EntityManagerInterface::class);
        }

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }
}