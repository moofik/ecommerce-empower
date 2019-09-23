<?php


namespace App\Tests\Unit\Service\Serializer\Groups;


use App\Serializer\Groups\GroupsResolver;
use JMS\Serializer\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GroupsResolverTest extends TestCase
{
    /**
     * @var GroupsResolver
     */
    private $groupsResolver;

    protected function setUp()
    {
        $request = new Request();
        $request->request->add(['groups' => 'user,tags']);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->groupsResolver = new GroupsResolver($requestStack);
    }

    public function testResolveGroups()
    {
        /** @var Context|MockObject $context */
        $context = $this->createMock(Context::class);
        $context
            ->expects($this->once())
            ->method('setGroups')
            ->with(['Default', 'user', 'tags']);

        $this->groupsResolver->resolveGroups($context);
    }

    public function testResolveGroupsWithNoDefaults()
    {
        /** @var Context|MockObject $context */
        $context = $this->createMock(Context::class);
        $context
            ->expects($this->once())
            ->method('setGroups')
            ->with(['user', 'tags']);

        $this->groupsResolver->resetDefaults();
        $this->groupsResolver->resolveGroups($context);
    }

    public function testResolveAddRequiredGroups()
    {
        /** @var Context|MockObject $context */
        $context = $this->createMock(Context::class);
        $context
            ->expects($this->once())
            ->method('setGroups')
            ->with(['Default', 'random', 'user', 'tags']);

        $this->groupsResolver->addGroup('random');
        $this->groupsResolver->resolveGroups($context);
    }
}