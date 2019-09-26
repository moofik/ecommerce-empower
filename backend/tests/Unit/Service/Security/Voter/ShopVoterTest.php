<?php

namespace App\Tests\Unit\Service\Security\Voter;

use App\Entity\Shop;
use App\Entity\User;
use App\Security\Voter\ShopVoter;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;

class ShopVoterTest extends TestCase
{
    public function testVoteOnShopOwner()
    {
        /** @var Security|MockObject $security */
        $security = $this->createMock(Security::class);
        $security
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false);

        $user = new User();
        $user->setUsername('Test');
        $token = new JWTUserToken(['ROLE_USER'], $user);

        $shop = $this->createMock(Shop::class);
        $shop
            ->method('getUser')
            ->willReturn($user);

        $shopVoter = new ShopVoter($security);
        $accessCode = $shopVoter->vote($token, $shop, ['SHOP_MANAGE']);

        $this->assertEquals(ShopVoter::ACCESS_GRANTED, $accessCode);
    }

    public function testVoteOnAdmin()
    {
        /** @var Security|MockObject $security */
        $security = $this->createMock(Security::class);
        $security
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        /** @var User|MockObject $user */
        $user = $this->createMock(User::class);
        $user
            ->method('getId')
            ->willReturn(1);

        /** @var User|MockObject $user */
        $anotherUser = $this->createMock(User::class);
        $anotherUser
            ->method('getId')
            ->willReturn(9999999);

        /** @var Shop|MockObject $shop */
        $shop = $this->createMock(Shop::class);
        $shop
            ->method('getUser')
            ->willReturn($anotherUser);

        $shopVoter = new ShopVoter($security);
        $token = new JWTUserToken(['ROLE_ADMIN'], $user);
        $accessCode = $shopVoter->vote($token, $shop, ['SHOP_MANAGE']);

        $this->assertEquals(ShopVoter::ACCESS_GRANTED, $accessCode);
    }

    public function testVoteOnUnknownUser()
    {
        /** @var Security|MockObject $security */
        $security = $this->createMock(Security::class);
        $security
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false);

        /** @var User|MockObject $user */
        $user = $this->createMock(User::class);
        $user
            ->method('getId')
            ->willReturn(1);

        /** @var User|MockObject $user */
        $anotherUser = $this->createMock(User::class);
        $anotherUser
            ->method('getId')
            ->willReturn(9999999);

        /** @var Shop|MockObject $shop */
        $shop = $this->createMock(Shop::class);
        $shop
            ->method('getUser')
            ->willReturn($anotherUser);

        $shopVoter = new ShopVoter($security);
        $token = new JWTUserToken(['ROLE_USER'], $user);
        $accessCode = $shopVoter->vote($token, $shop, ['SHOP_MANAGE']);

        $this->assertEquals(ShopVoter::ACCESS_DENIED, $accessCode);
    }
}
