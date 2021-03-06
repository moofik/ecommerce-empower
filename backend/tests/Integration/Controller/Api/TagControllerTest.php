<?php

/** @noinspection ALL */

namespace App\Tests\Integration\Controller\Api;

use App\Service\Api\Problem\ApiProblem;
use App\Tests\Integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class TagControllerTest extends ApiTestCase
{
    /**
     * @var array
     */
    private $adminAuthHeaders;

    /**
     * @var array
     */
    private $userAuthHeaders;

    protected function setUp()
    {
        parent::setUp();

        if (!$this->adminAuthHeaders) {
            $this->adminAuthHeaders = $this->getValidAuthenticationHeaders('admin', 'admin', ['ROLE_ADMIN']);
        }

        if (!$this->userAuthHeaders) {
            $this->userAuthHeaders = $this->getValidAuthenticationHeaders('user', 'user');
        }
    }

    public function testGetAllTags()
    {
        $this->createTags('tag', 0, 25);

        $response = $this->staticClient->request('GET', '/api/tags', [], [], $this->userAuthHeaders);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        $this->asserter()->assertResponsePropertyEquals($response, 'total', 25);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[0].name', 'tag0');
        $this->asserter()->assertResponsePropertyEquals($response, 'items[1].name', 'tag1');
        $this->asserter()->assertResponsePropertyEquals($response, 'items[2].name', 'tag2');
        $this->asserter()->assertResponsePropertyExists($response, '_links.next');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, '_links.prev');

        $nextUrl = $this->asserter()->readResponseProperty($response, '_links.next');
        $response = $this->staticClient->request('GET', $nextUrl, [], [], $this->userAuthHeaders);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[0].name', 'tag10');
        $this->asserter()->assertResponsePropertyExists($response, '_links.next');
        $this->asserter()->assertResponsePropertyExists($response, '_links.prev');

        $lastUrl = $this->asserter()->readResponseProperty($response, '_links.last');
        $response = $this->staticClient->request('GET', $lastUrl, [], [], $this->userAuthHeaders);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 5);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[4].name', 'tag24');
        $this->asserter()->assertResponsePropertyExists($response, '_links.prev');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, '_links.next');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'items[5].name');
    }

    public function testCreateAndGetOneTag()
    {
        $response = $this->createTag('milk');

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('/api/tags/milk', $response->headers->get('Location'));

        $response = $this->staticClient->request('GET', '/api/tags/milk', [], [], $this->userAuthHeaders);
        $this->asserter()->assertResponsePropertyEquals($response, 'name', 'milk');
        $this->asserter()->assertResponsePropertyEquals($response, 'slug', 'milk');
    }

    public function testCreateMultipleTagsWithSameName()
    {
        $response_1 = $this->createTag('milk');
        $response_2 = $this->createTag('milk');

        $this->assertEquals(201, $response_1->getStatusCode());
        $this->assertEquals('/api/tags/milk', $response_1->headers->get('Location'));
        $this->assertEquals(201, $response_2->getStatusCode());
        $this->assertEquals('/api/tags/milk-1', $response_2->headers->get('Location'));

        $response = $this->staticClient->request('GET', '/api/tags/milk', [], [], $this->userAuthHeaders);
        $this->asserter()->assertResponsePropertyEquals($response, 'name', 'milk');
        $this->asserter()->assertResponsePropertyEquals($response, 'slug', 'milk');

        $response = $this->staticClient->request('GET', '/api/tags/milk-1', [], [], $this->userAuthHeaders);
        $this->asserter()->assertResponsePropertyEquals($response, 'name', 'milk');
        $this->asserter()->assertResponsePropertyEquals($response, 'slug', 'milk-1');
    }

    public function testDeleteTag()
    {
        $response_1 = $this->createTag('milk');
        $response_2 = $this->staticClient->request('DELETE', '/api/tags/milk', [], [], $this->adminAuthHeaders);
        $response_3 = $this->staticClient->request('GET', '/api/tags/milk', [], [], $this->userAuthHeaders);

        $this->assertEquals(204, $response_2->getStatusCode());
        $this->assertEquals(404, $response_3->getStatusCode());
    }

    public function testDeleteTagIsAllowedOnlyForAdminUsers()
    {
        $response_1 = $this->createTag('milk');
        $response_2 = $this->staticClient->request('DELETE', '/api/tags/milk', [], [], $this->userAuthHeaders);
        $response_3 = $this->staticClient->request('DELETE', '/api/tags/milk');

        $this->assertEquals(403, $response_2->getStatusCode());
        $this->assertEquals(401, $response_3->getStatusCode());
    }

    public function testTagValidationErrors()
    {
        // can not have empty name
        $response = $this->createTag('');

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'type',
            'title',
            'errors',
        ]);
        $this->asserter()->assertResponsePropertyExists($response, 'errors.name');
        $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
    }

    public function testCreateWithInvalidJson()
    {
        $content = <<<'EOF'
{
    "name": "milk
}
EOF;

        $response = $this->staticClient->request('POST', '/api/tags', [], [], $this->adminAuthHeaders, $content);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
        $this->asserter()->assertResponsePropertyContains(
            $response,
            'type',
            ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT
        );
    }

    public function testGetNonExistantTag()
    {
        $response = $this->staticClient->request('GET', '/api/tags/test', [], [], $this->userAuthHeaders);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Not Found');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'Tag with slug test was not found');
    }

    public function testTagCreatingRequiresAuthenticationAndAdminRole()
    {
        $response = $this->staticClient->jsonRequest('POST', '/api/tags', ['name' => 'test']);
        $this->assertEquals(401, $response->getStatusCode());

        $response = $this->staticClient->jsonRequest('POST', '/api/tags', ['name' => 'test'], $this->userAuthHeaders);
        $this->assertEquals(403, $response->getStatusCode());
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

    private function createTags(string $name, int $from, int $to)
    {
        for ($i = $from; $i < $to; $i++) {
            $this->createTag($name.$i);
        }
    }
}
