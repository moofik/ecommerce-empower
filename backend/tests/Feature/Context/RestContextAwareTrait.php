<?php


namespace App\Tests\Feature\Context;


use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behatch\Context\RestContext;

trait RestContextAwareTrait
{
    /**
     * @var RestContext
     */
    private $restContext;

    /**
     * @BeforeScenario
     * @param BeforeScenarioScope $scope
     */
    public function gatherRestContext(BeforeScenarioScope $scope)
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();

        try {
            /** @var RestContext restContext */
            $this->restContext = $environment->getContext(RestContext::class);
        } catch (ContextNotFoundException $exception) {
            throw new \RuntimeException('You should add '.RestContext::class.' to your test suite.');
        }
    }

    /**
     * @Given I set request header :name to :value
     * @param string $name
     * @param string $value
     */
    public function iSetRequestHeaderTo(string $name, string $value)
    {
        $this->restContext->iAddHeaderEqualTo($name, $value);
    }
}