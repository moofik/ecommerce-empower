<?php


namespace App\Tests\Unit\Service\Service\Maker;


use App\Entity\Shop;
use App\Entity\User;
use App\Repository\ShopRepository;
use App\Service\Shop\Maker\DefaultCreationStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class DefaultCreationStrategyTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ShopRepository
     */
    private $shopRepository;

    /**
     * @var DefaultCreationStrategy
     */
    private $defaultCreationStrategy;

    protected function setUp()
    {
        parent::setUp();
        $this->shopRepository = $this->createMock(ShopRepository::class);
        $this->defaultCreationStrategy = new DefaultCreationStrategy($this->shopRepository);
    }

    public function testCreateShopSuccessful()
    {
        $this->shopRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $user = new User();
        $user->setUsername('test');
        $shop = $this->defaultCreationStrategy->create($user);

        $this->assertEquals('test', $shop->getName());
        $this->assertEquals('Default', $shop->getDescription());
    }

    public function testCreateShopFailed()
    {
        $shop = new Shop();

        $this->shopRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($shop);

        $user = new User();
        $user->setUsername('test');

        try {
            $this->defaultCreationStrategy->create($user);
        } catch (ConflictHttpException $exception) {
            $this->assertEquals('You already have a shop.', $exception->getMessage());
        }
    }
}