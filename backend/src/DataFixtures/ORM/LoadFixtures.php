<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\CustomNativeLoader;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadFixtures extends Fixture
{
    /**
     * @var string
     */
    private $rootDirectory;

    /**
     * LoadFixtures constructor.
     *
     * @param string $rootDirectory
     */
    public function __construct($rootDirectory)
    {
        $this->rootDirectory = $rootDirectory;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $loader = new CustomNativeLoader();

        $objectSet = $loader->loadFile('fixtures/users.yaml')->getObjects();
        foreach ($objectSet as $object) {
            $manager->persist($object);
        }
        $manager->flush();

        $objectSet = $loader->loadFile('fixtures/categories.yaml')->getObjects();
        foreach ($objectSet as $object) {
            $manager->persist($object);
        }
        $manager->flush();

        $objectSet = $loader->loadFile('fixtures/subcategories.yaml')->getObjects();
        foreach ($objectSet as $object) {
            $manager->persist($object);
        }
        $manager->flush();
    }
}
