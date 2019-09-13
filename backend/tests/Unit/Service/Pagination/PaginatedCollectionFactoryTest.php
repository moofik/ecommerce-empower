<?php


namespace App\Tests\Unit\Service\Pagination;


use App\Service\Pagination\PaginatedCollection;
use App\Service\Pagination\PaginatedCollectionFactory;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class PaginatedCollectionFactoryTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\Builder\InvocationMocker|RouterInterface
     */
    private $router;

    /**
     * @var \PHPUnit\Framework\MockObject\Builder\InvocationMocker|QueryBuilder
     */
    private $qb;

    /**
     * @var \PHPUnit\Framework\MockObject\Builder\InvocationMocker|QueryBuilder
     */
    private $countQb;

    protected function setUp()
    {
        $this->router = $this->getMockForAbstractClass(RouterInterface::class);
        $this->router
            ->method('generate')
            ->willReturnArgument(0);

        $qbQuery = $this
            ->getMockBuilder('StubQuery') // can't mock Doctrine's "Query" because it's "final"
            ->setMethods(['getResult'])
            ->disableOriginalConstructor()
            ->getMock();
        $qbQuery
            ->method('getResult')
            ->willReturn([['name' => 'John Holland'], ['name' => 'Jack Donoghue']]);

        $this->qb = $this->createMock(QueryBuilder::class);
        $this->qb
            ->method('setMaxResults')
            ->willReturnSelf();
        $this->qb
            ->method('setFirstResult')
            ->willReturnSelf();
        $this->qb
            ->method('getQuery')
            ->willReturn($qbQuery);

        $countQbQuery = $this
            ->getMockBuilder('StubQuery') // can't mock Doctrine's "Query" because it's "final"
            ->setMethods(['getSingleScalarResult'])
            ->disableOriginalConstructor()
            ->getMock();
        $countQbQuery
            ->method('getSingleScalarResult')
            ->willReturn(2);

        $this->countQb = $this->createMock(QueryBuilder::class);
        $this->countQb
            ->method('setMaxResults')
            ->willReturnSelf();
        $this->countQb
            ->method('getQuery')
            ->willReturn($countQbQuery);
    }

    public function testCreateCollection()
    {
        $factory = new PaginatedCollectionFactory($this->router);
        $collection = $factory->createCollection($this->qb, $this->countQb, 'test', [], 1, 10);

        $countRef = (new \ReflectionProperty(PaginatedCollection::class, 'count'));
        $countRef->setAccessible(true);
        $count = $countRef->getValue($collection);

        $itemsRef = (new \ReflectionProperty(PaginatedCollection::class, 'items'));
        $itemsRef->setAccessible(true);
        $items = $itemsRef->getValue($collection);

        $totalRef = (new \ReflectionProperty(PaginatedCollection::class, 'total'));
        $totalRef->setAccessible(true);
        $total = $totalRef->getValue($collection);

        $this->assertEquals(2, $count);
        $this->assertEquals([['name' => 'John Holland'], ['name' => 'Jack Donoghue']], $items);
        $this->assertEquals(2, $total);
    }


}