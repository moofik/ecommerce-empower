<?php

namespace App\Tests\Integration;

use App\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
     * @param \Throwable $t
     *
     * @throws \Throwable
     */
    protected function onNotSuccessfulTest(\Throwable $t)
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
        self::$client->setServerParameter('CONTENT_TYPE', 'application/json');
        self::$debugger = new ApiTestCaseDebugger(self::$client);
    }

    public function tearDown()
    {
        //
    }

    /**
     * @return ResponseAsserter
     */
    protected function asserter()
    {
        if (null === self::$asserter) {
            self::$asserter = new ResponseAsserter();
        }

        return self::$asserter;
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

    /**
     * @return UserPasswordEncoderInterface
     */
    protected function getUsernamePasswordEncoder(): UserPasswordEncoderInterface
    {
        return self::$kernel
            ->getContainer()
            ->get('security.password_encoder');
    }

    /**
     * @param string $name
     * @param string $password
     * @param array  $roles
     *
     * @return User
     */
    protected function createUser(string $name, string $password, array $roles = []): User
    {
        $user = new User();
        $user->setUsername($name);
        $user->setPassword($this->getUsernamePasswordEncoder()->encodePassword($user, $password));
        $user->setEmail('test@colloseum.com');
        $user->setRoles($roles);

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * @param string $username
     * @param string $password
     * @param array  $roles
     *
     * @return array
     */
    protected function getValidAuthenticationHeaders(string $username = 'test', string $password = 'test', array $roles = []): array
    {
        $user = $this->createUser($username, $password, $roles);
        /** @var JWTTokenManagerInterface $jwtTokenManager */
        $jwtTokenManager = self::$kernel
            ->getContainer()
            ->get('lexik_jwt_authentication.jwt_manager');

        $token = $jwtTokenManager->create($user);

        $headers = [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'CONTENT_TYPE'       => 'application/json',
        ];

        return $headers;
    }
}
