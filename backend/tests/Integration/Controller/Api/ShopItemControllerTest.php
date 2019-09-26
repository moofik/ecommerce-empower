<?php

/** @noinspection ALL */

namespace App\Tests\Integration\Controller\Api;

use App\Service\Api\Problem\ApiProblem;
use App\Tests\Integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ShopItemControllerTest extends ApiTestCase
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

        $this->createTag('food');
        $this->createTag('onion');
        $this->createTag('sour');
        $this->createTag('drink');
        $this->createTag('sweet');
        $this->createTag('pink');
        $this->createTag('luxury');
    }

    public function testGetAllItems()
    {
        $shop = $this->getShop($this->userAuthHeaders);
        $this->createTag('cigarette');
        $this->createTag('babylon');

        $this->createItem([
            'name'               => 'Rock Star Real Estate',
            'description'        => 'One of the best things you ever had',
            'priceType'          => 'ranged',
            'priceMin'           => 1000,
            'priceMax'           => 2500,
            'isBargainPossible'  => false,
            'isExchangePossible' => false,
            'tags'               => ['cigarette'],
        ], $shop->id, $this->userAuthHeaders);

        $this->createItem([
            'name'               => 'Smart Card',
            'description'        => 'Who knows what is the purpose of this strage thing...',
            'priceType'          => 'fixed',
            'priceMin'           => 400,
            'priceMax'           => null,
            'isBargainPossible'  => true,
            'isExchangePossible' => true,
            'tags'               => ['babylon'],
        ], $shop->id, $this->userAuthHeaders);

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
        $shop = $this->getShop($this->userAuthHeaders);

        $properties = [
            'name'               => 'Rocket Milk Diesel Drink',
            'description'        => 'Luxury milk for beauty luxury super incredible super futuristic girls',
            'priceType'          => 'ranged',
            'priceMin'           => 1000,
            'priceMax'           => 2500,
            'isBargainPossible'  => false,
            'isExchangePossible' => false,
            'tags'               => ['food', 'pink', 'luxury'],
        ];

        $response = $this->createItem($properties, $shop->id, $this->userAuthHeaders);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals("/api/shops/{$shop->id}/items/rocket-milk-diesel-drink", $response->headers->get('Location'));
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'user.username');
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[0].name', 'food');
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[1].name', 'pink');
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[2].name', 'luxury');
    }

    public function testItemWithEmbeddedFields()
    {
        $shop = $this->getShop($this->userAuthHeaders);

        $properties = [
            'name'               => 'Rocket Milk Diesel Drink',
            'description'        => 'Luxury milk for beauty luxury super incredible super futuristic girls',
            'priceType'          => 'ranged',
            'priceMin'           => 1000,
            'priceMax'           => 2500,
            'isBargainPossible'  => false,
            'isExchangePossible' => false,
            'tags'               => ['food', 'pink', 'luxury'],
        ];

        $response = $this->createItem($properties, $shop->id, $this->userAuthHeaders);
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
        $shop = $this->getShop($this->userAuthHeaders);
        $response = $this->createItem([
            'name'               => 'Rocket Sour Onion Drink',
            'description'        => 'Luxury onion for sour lowriders',
            'priceType'          => 'ranged',
            'priceMin'           => 1000,
            'priceMax'           => 2500,
            'isBargainPossible'  => false,
            'isExchangePossible' => false,
            'tags'               => ['food', 'onion', 'sour'],
        ], $shop->id, $this->userAuthHeaders);

        $response = $this->staticClient->request('DELETE', $response->headers->get('Location'), [], [], $this->userAuthHeaders);

        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testNonShopOwnerCantDeleteItem()
    {
        $shop = $this->getShop($this->userAuthHeaders);
        $response = $this->createItem([
            'name'               => 'Rocket Sour Onion Drink',
            'description'        => 'Luxury onion for sour lowriders',
            'priceType'          => 'ranged',
            'priceMin'           => 1000,
            'priceMax'           => 2500,
            'isBargainPossible'  => false,
            'isExchangePossible' => false,
            'tags'               => ['food', 'onion', 'sour'],
        ], $shop->id, $this->userAuthHeaders);

        $response = $this->staticClient->request('DELETE', $response->headers->get('Location'), [], [], $this->anotherUserAuthHeaders);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testAdminCanDeleteEveryItem()
    {
        $shop_1 = $this->getShop($this->userAuthHeaders);
        $response_1 = $this->createItem([
            'name'               => 'Rocket Sour Onion Drink',
            'description'        => 'Luxury onion for sour lowriders',
            'priceType'          => 'ranged',
            'priceMin'           => 1000,
            'priceMax'           => 2500,
            'isBargainPossible'  => false,
            'isExchangePossible' => false,
            'tags'               => ['food', 'onion', 'sour'],
        ], $shop_1->id, $this->userAuthHeaders);

        $shop_2 = $this->getShop($this->anotherUserAuthHeaders);
        $response_2 = $this->createItem([
            'name'               => 'Rocket Sour Onion Drink',
            'description'        => 'Luxury onion for sour lowriders',
            'priceType'          => 'ranged',
            'priceMin'           => 1000,
            'priceMax'           => 2500,
            'isBargainPossible'  => false,
            'isExchangePossible' => false,
            'tags'               => ['food', 'onion', 'sour'],
        ], $shop_2->id, $this->anotherUserAuthHeaders);

        $deleteResponse_1 = $this->staticClient->request('DELETE', $response_1->headers->get('Location'), [], [], $this->adminAuthHeaders);
        $deleteResponse_2 = $this->staticClient->request('DELETE', $response_2->headers->get('Location'), [], [], $this->adminAuthHeaders);

        $this->assertEquals(204, $deleteResponse_1->getStatusCode());
        $this->assertEquals(204, $deleteResponse_2->getStatusCode());
    }

    public function testNonShopOwnerCantCreateItem()
    {
        $shop = $this->getShop($this->anotherUserAuthHeaders);
        $response = $this->createItem([], $shop->id, $this->userAuthHeaders);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testShopOwnerCanEditItem()
    {
        $shop = $this->getShop($this->userAuthHeaders);

        $response = $this->createItem([
            'name'               => 'Rocket Sour Onion Drink',
            'description'        => 'Luxury onion for sour lowriders',
            'priceType'          => 'ranged',
            'priceMin'           => 1000,
            'priceMax'           => 2500,
            'isBargainPossible'  => false,
            'isExchangePossible' => false,
            'tags'               => ['food', 'onion', 'sour'],
        ], $shop->id, $this->userAuthHeaders);

        $response = $this->staticClient->jsonRequest(
            'PATCH',
            $response->headers->get('Location'),
            [
                'name' => 'Chupa Chups Drink',
                'tags' => ['drink', 'sweet'],
            ],
            $this->userAuthHeaders
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[0].name', 'drink');
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[1].name', 'sweet');
        $this->asserter()->assertResponsePropertyCount($response, 'tags', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'name', 'Chupa Chups Drink');
    }

    public function testPutItemEdit()
    {
        $shop = $this->getShop($this->userAuthHeaders);

        $response = $this->createItem([
            'name'               => 'Rocket Sour Onion Drink',
            'description'        => 'Luxury onion for sour lowriders',
            'priceType'          => 'ranged',
            'priceMin'           => 1000,
            'priceMax'           => 2500,
            'isBargainPossible'  => false,
            'isExchangePossible' => false,
            'tags'               => ['food', 'onion', 'sour'],
        ], $shop->id, $this->userAuthHeaders);

        $location = $response->headers->get('Location');

        $response = $this->staticClient->jsonRequest(
            'PUT',
            $location,
            [
                'name' => 'Chupa Chups Drink',
                'tags' => ['drink', 'sweet'],
            ],
            $this->userAuthHeaders
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'type', ApiProblem::TYPE_VALIDATION_ERROR);
        $this->asserter()->assertResponsePropertyExists($response, 'detail');

        $response = $this->staticClient->jsonRequest(
            'PUT',
            $location,
            [
                'name'               => 'Rocket Sour Onion Drink',
                'description'        => 'Luxury onion for sour lowriders',
                'priceType'          => 'fixed',
                'priceMin'           => 1000,
                'priceMax'           => 2500,
                'isBargainPossible'  => false,
                'isExchangePossible' => false,
                'tags'               => ['drink', 'sweet'],
            ],
            $this->userAuthHeaders
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'priceType', 'fixed');
        $this->asserter()->assertResponsePropertyExists($response, 'tags[0].name', 'drink');
        $this->asserter()->assertResponsePropertyExists($response, 'tags[0].name', 'sweet');
    }

    public function testNonShopOwnerCantEditItem()
    {
        $shop = $this->getShop($this->userAuthHeaders);

        $response = $this->createItem([
            'name'               => 'Rocket Sour Onion Drink',
            'description'        => 'Luxury onion for sour lowriders',
            'priceType'          => 'ranged',
            'priceMin'           => 1000,
            'priceMax'           => 2500,
            'isBargainPossible'  => false,
            'isExchangePossible' => false,
            'tags'               => ['food', 'onion', 'sour'],
        ], $shop->id, $this->userAuthHeaders);

        $response = $this->staticClient->jsonRequest(
            'PATCH',
            $response->headers->get('Location'),
            [
                'name' => 'Chupa Chups Drink',
                'tags' => ['drink', 'sweet'],
            ],
            $this->anotherUserAuthHeaders
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testAdminCanEditEveryItem()
    {
        $shop = $this->getShop($this->userAuthHeaders);

        $response = $this->createItem([
            'name'               => 'Rocket Sour Onion Drink',
            'description'        => 'Luxury onion for sour lowriders',
            'priceType'          => 'ranged',
            'priceMin'           => 1000,
            'priceMax'           => 2500,
            'isBargainPossible'  => false,
            'isExchangePossible' => false,
            'tags'               => ['food', 'onion', 'sour'],
        ], $shop->id, $this->userAuthHeaders);

        $response = $this->staticClient->jsonRequest(
            'PATCH',
            $response->headers->get('Location'),
            [
                'name' => 'Chupa Chups Drink',
                'tags' => ['drink', 'sweet'],
            ],
            $this->adminAuthHeaders
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[0].name', 'drink');
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[1].name', 'sweet');
        $this->asserter()->assertResponsePropertyCount($response, 'tags', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'name', 'Chupa Chups Drink');
    }

    public function testGetItem()
    {
        $shop = $this->getShop($this->userAuthHeaders);
        $response = $this->createItem([
            'name'               => 'Rocket Sour Onion Drink',
            'description'        => 'Luxury onion for sour lowriders',
            'priceType'          => 'ranged',
            'priceMin'           => 1000,
            'priceMax'           => 2500,
            'isBargainPossible'  => false,
            'isExchangePossible' => false,
            'tags'               => ['food', 'onion', 'sour'],
        ], $shop->id, $this->userAuthHeaders);

        $itemUrl = $response->headers->get('Location');
        $response = $this->staticClient->request('GET', $itemUrl, [], [], $this->anotherUserAuthHeaders);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'name', 'Rocket Sour Onion Drink');
        $this->asserter()->assertResponsePropertyEquals($response, 'description', 'Luxury onion for sour lowriders');
        $this->asserter()->assertResponsePropertyEquals($response, 'priceType', 'ranged');
        $this->asserter()->assertResponsePropertyEquals($response, 'priceMin', 1000);
        $this->asserter()->assertResponsePropertyEquals($response, 'priceMax', 2500);
        $this->asserter()->assertResponsePropertyEquals($response, 'isBargainPossible', false);
        $this->asserter()->assertResponsePropertyEquals($response, 'isExchangePossible', false);
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[0].name', 'food');
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[1].name', 'onion');
        $this->asserter()->assertResponsePropertyEquals($response, 'tags[2].name', 'sour');
    }

    /**
     * @param array $properties
     * @param int   $shopId
     * @param array $authHeaders
     *
     * @return Response
     */
    private function createItem(array $properties, int $shopId, array $authHeaders): Response
    {
        $uri = '/api/shops/'.$shopId.'/items';

        return $this->staticClient->jsonRequest('POST', $uri, $properties, $authHeaders);
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
