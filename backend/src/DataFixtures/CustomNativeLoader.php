<?php

namespace App\DataFixtures;

use Faker\Factory as FakerGeneratorFactory;
use Faker\Generator as FakerGenerator;
use Nelmio\Alice\Faker\Provider\AliceProvider;
use Nelmio\Alice\Loader\NativeLoader;

class CustomNativeLoader extends NativeLoader
{
    protected function createFakerGenerator(): FakerGenerator
    {
        $generator = FakerGeneratorFactory::create(parent::LOCALE);
        $generator->addProvider(new AliceProvider());
        $generator->addProvider(new UserProvider());
        $generator->addProvider(new CategoryProvider());
        $generator->seed($this->getSeed());

        return $generator;
    }
}
