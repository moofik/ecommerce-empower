<?php

/** @noinspection ALL */

namespace App\Tests\Integration\Controller\Api;

use App\Tests\Integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ItemControllerTest extends ApiTestCase
{
    /**
     * @var array
     */
    private $adminAuthHeaders;

    /**
     * @var array
     */
    private $anotherUserAuthHeaders;

    /**
     * @var array
     */
    private $userAuthHeaders;

    protected function setUp()
    {
        parent::setUp();

        if (!$this->anotherUserAuthHeaders) {
            $this->anotherUserAuthHeaders = $this->getValidAuthenticationHeaders('user2', 'user2');
        }

        if (!$this->userAuthHeaders) {
            $this->userAuthHeaders = $this->getValidAuthenticationHeaders('user', 'user');
        }

        if (!$this->adminAuthHeaders) {
            $this->adminAuthHeaders = $this->getValidAuthenticationHeaders('admin', 'admin', ['ROLE_ADMIN']);
        }
    }

    public function testGetAllItems()
    {
        $shop = $this->getShop($this->userAuthHeaders);

        $this->createItem([
            'name' => 'Rock Star Real Estate',
            'description' => 'One of the best things you ever had',
            'priceType' => 'ranged',
            'priceMin' => 1000,
            'priceMax' => 2500,
            'isBargainPossible' => false,
            'isExchangePossible' => false,
            'tags' => ['cigarette'],
        ], $shop->id);

        $this->createItem([
            'name' => 'Smart Card',
            'description' => 'Who knows what is the purpose of this strage thing...',
            'priceType' => 'fixed',
            'priceMin' => 400,
            'priceMax' => null,
            'isBargainPossible' => true,
            'isExchangePossible' => true,
            'tags' => ['babylon'],
        ], $shop->id);

        $response = $this->staticClient->request('GET', "/api/shops/{$shop->id}/items", ['count' => 1], [], $this->userAuthHeaders);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 1);
        $this->asserter()->assertResponsePropertyEquals($response, 'total', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[0].name', 'Rock Star Real Estate');
        $this->asserter()->assertResponsePropertyExists($response, '_links.next');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, '_links.prev');

        $nextUrl = $this->asserter()->readResponseProperty($response, '_links.next');

        $response = $this->staticClient->request('GET', $nextUrl, ['count' => 1], [], $this->userAuthHeaders);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 1);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[0].name', 'Smart Card');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, '_links.next');
        $this->asserter()->assertResponsePropertyExists($response, '_links.prev');
    }

    public function testCreateItemWithTags()
    {
        $this->createTag('food');
        $this->createTag('pink');
        $this->createTag('luxury');

        $shop = $this->getShop($this->userAuthHeaders);

        $properties = [
            'name' => 'Rocket Milk Diesel Drink',
            'description' => 'Luxury milk for beauty luxury super incredible super futuristic girls',
            'priceType' => 'ranged',
            'priceMin' => 1000,
            'priceMax' => 2500,
            'isBargainPossible' => false,
            'isExchangePossible' => false,
            'tags' => ['food', 'pink', 'luxury']
        ];

        $response = $this->createItem($properties, $shop->id);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals("/api/shops/{$shop->id}/items/rocket-milk-diesel-drink", $response->headers->get('Location'));
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'user.username');
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[0].name', 'food');
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[1].name', 'pink');
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[2].name', 'luxury');
    }

    public function testItemWithEmbeddedFields()
    {
        $this->createTag('food');
        $this->createTag('pink');
        $this->createTag('luxury');

        $shop = $this->getShop($this->userAuthHeaders);

        $properties = [
            'name' => 'Rocket Milk Diesel Drink',
            'description' => 'Luxury milk for beauty luxury super incredible super futuristic girls',
            'priceType' => 'ranged',
            'priceMin' => 1000,
            'priceMax' => 2500,
            'isBargainPossible' => false,
            'isExchangePossible' => false,
            'tags' => ['food', 'pink', 'luxury']
        ];

        $response = $this->createItem($properties, $shop->id);
        $location = $response->headers->get('Location');
        $response = $this->staticClient->request(
            'GET',
            $location.'?groups=shop,user',
            [],
            [],
            $this->userAuthHeaders
        );
        $this->assertEquals('200', $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[0].name', 'food');
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[1].name', 'pink');
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[2].name', 'luxury');
        $this->asserter()->assertResponsePropertyExists($response, 'shop.user.username');

        $response = $this->staticClient->request('GET', $location, [], [], $this->userAuthHeaders);
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'shop.name');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'shop.user');
    }

    public function testShopOwnerCanDeleteItem()
    {

    }

    public function testNonShopOwnerCantDeleteItem()
    {

    }

    public function testAdminCanDeleteEveryItem()
    {

    }

    public function testNonShopOwnerCantCreateItem()
    {
        $shop = $this->getShop($this->anotherUserAuthHeaders);
        $response = $this->createItem([], $shop->id);

        $this->assertEquals(403, $response->getStatusCode());
    }
//
//    public function testShopOwnerCanEditItem()
//    {
//
//    }
//
//    public function testNonShopOwnerCantEditItem()
//    {
//
//    }
//
//    public function testAdminCanEditEveryItem()
//    {
//
//    }

    /**
     * @param string $name
     *
     * @throws TransportExceptionInterface
     *
     * @return Response
     */
    private function createItem(array $properties, int $shopId): Response
    {
        $uri = '/api/shops/'.$shopId.'/items';
        return $this->staticClient->jsonRequest('POST', $uri, $properties, $this->userAuthHeaders);
    }

    /**
     * @param string $name
     *
     * @throws TransportExceptionInterface
     *
     * @return Response
     */
    private function createTag(string $name): Response
    {
        return $this->staticClient->jsonRequest('POST', '/api/tags', ['name' => $name], $this->adminAuthHeaders);
    }

    private function getShop(array $authHeaders): object
    {
        $shopResponse = $this->staticClient->jsonRequest('POST', '/api/shops', [], $authHeaders);

        return json_decode($shopResponse->getContent());
    }
}
