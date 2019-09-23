<?php


namespace App\Tests\Unit\Service\Command;


use App\Tests\Integration\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * We use ApiTestCase because of nice setUp implementation which purges our database before executing test
 */
class CreateUserCommandTest extends ApiTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('app:create:user');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['bucket', 'hackme@ifyou.can', 'sunny', 'y']);
        $commandTester->execute(['command'  => $command->getName()]);

        $output = $commandTester->getDisplay();
        $this->assertContains('I generated API token for this user', $output);
    }
}