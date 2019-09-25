<?php


namespace App\Tests\Integration\Controller\Api;


use App\Tests\Integration\ApiTestCase;

class ShopControllerTest extends ApiTestCase
{
    /**
     * @var array
     */
    private $userAuthHeaders;

    /**
     * @var array
     */
    private $anotherUserAuthHeaders;

    /**
     * @var array
     */
    private $adminUserAuthHeaders;

    protected function setUp()
    {
        parent::setUp();
        $this->userAuthHeaders = $this->getValidAuthenticationHeaders('user', 'user');
        $this->anotherUserAuthHeaders = $this->getValidAuthenticationHeaders('another', 'another');
        $this->adminUserAuthHeaders = $this->getValidAuthenticationHeaders('admin', 'admin', ['ROLE_ADMIN']);
    }

    public function testCreateShop()
    {
        $response = $this->staticClient->jsonRequest('POST', '/api/shops', [], $this->userAuthHeaders);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertContains('/api/shops/', $response->headers->get('Location'));
    }

    public function testThatUserCanCreateShopJustOnce()
    {
        $this->staticClient->jsonRequest('POST', '/api/shops', [], $this->userAuthHeaders);
        $response = $this->staticClient->jsonRequest('POST', '/api/shops', [], $this->userAuthHeaders);

        $this->assertEquals(409, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'You already have a shop.');
    }

    public function testCreateShopForNonAuthorizedUser()
    {
        $response = $this->staticClient->jsonRequest('POST', '/api/shops', []);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testGetShopForNonAuthorizedUser()
    {
        $response = $this->staticClient->jsonRequest('POST', '/api/shops', [], $this->userAuthHeaders);
        $shopUrl = $response->headers->get('Location');
        $response = $this->staticClient->request('GET', $shopUrl);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'name', 'user');
        $this->asserter()->assertResponsePropertyExists($response, 'description');
    }

    public function testGetShopWithGroups()
    {
        $response = $this->staticClient->jsonRequest('POST', '/api/shops', [], $this->userAuthHeaders);
        $shopUrl = $response->headers->get('Location');
        $response = $this->staticClient->request('GET', $shopUrl.'?groups=user');

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'user.username', 'user');
    }

    public function testDeleteShopByShopOwner()
    {
        $response = $this->staticClient->jsonRequest('POST', '/api/shops', [], $this->userAuthHeaders);
        $shopUrl = $response->headers->get('Location');
        $response_1 = $this->staticClient->jsonRequest('DELETE', $shopUrl, [], $this->userAuthHeaders);
        $response_2 = $this->staticClient->jsonRequest('DELETE', $shopUrl, [], $this->userAuthHeaders);

        $this->assertEquals(204, $response_1->getStatusCode());
        $this->assertEquals(404, $response_2->getStatusCode());
    }

    public function testDeleteShopByAnotherUser()
    {
        $response = $this->staticClient->jsonRequest('POST', '/api/shops', [], $this->userAuthHeaders);
        $shopUrl = $response->headers->get('Location');
        $response = $this->staticClient->jsonRequest('DELETE', $shopUrl, [], $this->anotherUserAuthHeaders);

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testDeleteShopByAdminUser()
    {
        $response = $this->staticClient->jsonRequest('POST', '/api/shops', [], $this->userAuthHeaders);
        $shopUrl = $response->headers->get('Location');
        $response = $this->staticClient->jsonRequest('DELETE', $shopUrl, [], $this->adminUserAuthHeaders);

        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testDeleteShopForNonAuthorizedUser()
    {
        $response = $this->staticClient->jsonRequest('POST', '/api/shops', [], $this->userAuthHeaders);
        $shopUrl = $response->headers->get('Location');
        $response = $this->staticClient->jsonRequest('DELETE', $shopUrl, []);

        $this->assertEquals(401, $response->getStatusCode());
    }
}