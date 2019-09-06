<?php


namespace App\Tests\Integration;


use App\Tests\Integration\ResponseAsserter;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
use Throwable;


class ApiTestCase extends WebTestCase
{
    /**
     * @var ApiClient
     */
    protected static $client;

    /**
     * @var ApiTestCaseDebugger
     */
    protected static $debugger;

    /**
     * @var ApiClient
     */
    protected $staticClient;

    /**
     * @var ResponseAsserter
     */
    protected static $asserter;

    /**
     * @param Throwable $t
     * @throws Throwable
     */
    protected function onNotSuccessfulTest(Throwable $t)
    {
        if ($response = self::$debugger->getLastResponse()) {
            self::$debugger->printDebug('');
            self::$debugger->printDebug('<error>Failure!</error> when making following request:');
            self::$debugger->printLastRequestUrl();
            self::$debugger->printDebug('');

            self::$debugger->debugResponse($response);
        }

        throw $t;
    }

    protected function setUp()
    {
        $this->staticClient = self::$client;
        $this->purgeDatabase();
    }

    public static function setUpBeforeClass()
    {
        parent::bootKernel();

        self::$client = new ApiClient(self::$kernel);
        self::$debugger = new ApiTestCaseDebugger(self::$client);
    }

    /**
     * @return ResponseAsserter
     */
    public function asserter()
    {
        if (null === self::$asserter) {
            self::$asserter = new ResponseAsserter();
        }

        return self::$asserter;
    }

    public function tearDown()
    {
    }

    protected function purgeDatabase()
    {
        $purger = new ORMPurger($this->getEntityManager());
        $purger->purge();
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return self::$kernel
            ->getContainer()
            ->get('doctrine')
            ->getManager();
    }
}