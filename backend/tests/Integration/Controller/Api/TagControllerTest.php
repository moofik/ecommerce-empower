<?php /** @noinspection ALL */


namespace App\Tests\Integration\Controller\Api;


use App\Service\Api\Problem\ApiProblem;
use App\Tests\Integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class TagControllerTest extends ApiTestCase
{
    public function testGetAllTags()
    {
        $this->createTag('icecream');
        $this->createTag('nofilter');
        $this->createTag('peacock');

        $response = $this->staticClient->request("GET", '/api/tags');

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyCount($response, 'items', 3);
        $this->asserter()->assertResponsePropertyEquals($response,'items[0].name', 'icecream');
        $this->asserter()->assertResponsePropertyEquals($response,'items[1].name', 'nofilter');
        $this->asserter()->assertResponsePropertyEquals($response,'items[2].name', 'peacock');
    }

    public function testCreateAndGetOneTag()
    {
        $response = $this->createTag('milk');

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('/api/tag/milk', $response->headers->get('Location'));

        $response = self::$client->request('GET', '/api/tag/milk');
        $this->asserter()->assertResponsePropertyEquals($response, 'name', 'milk');
        $this->asserter()->assertResponsePropertyEquals($response, 'slug', 'milk');
    }

    public function testCreateMultipleTagsWithSameName()
    {
        $response_1 = $this->createTag('milk');
        $response_2 = $this->createTag('milk');

        $this->assertEquals(201, $response_1->getStatusCode());
        $this->assertEquals('/api/tag/milk', $response_1->headers->get('Location'));
        $this->assertEquals(201, $response_2->getStatusCode());
        $this->assertEquals('/api/tag/milk-1', $response_2->headers->get('Location'));

        $response = self::$client->request('GET', '/api/tag/milk');
        $this->asserter()->assertResponsePropertyEquals($response, 'name', 'milk');
        $this->asserter()->assertResponsePropertyEquals($response, 'slug', 'milk');

        $response = self::$client->request('GET', '/api/tag/milk-1');
        $this->asserter()->assertResponsePropertyEquals($response, 'name', 'milk');
        $this->asserter()->assertResponsePropertyEquals($response, 'slug', 'milk-1');
    }

//    public function testDeleteTag()
//    {
//        $response_1 = $this->createTag('milk');
//        $response_2 = self::$client->request('DELETE', '/api/tag/milk');
//        $response_3 = self::$client->request('GET', '/api/tag/milk');
//
//        $this->assertEquals(204, $response_2->getStatusCode());
//        $this->assertEquals(404, $response_3->getStatusCode());
//    }

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
        $content = <<<EOF
{
    "name": "milk
}
EOF;

        $response = self::$client->request('POST', '/api/tag', [], [], [], $content);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'type',
            ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT
        );
    }

    public function testGetNonExistantTag()
    {
        $response = self::$client->request('GET', '/api/tag/test');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Not Found');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'Tag with slug test was not found');
    }

    /**
     * @param string $name
     * @throws TransportExceptionInterface
     * @return Response
     */
    private function createTag(string $name): Response
    {
        return $this->staticClient->jsonRequest("POST", '/api/tag', ['name' => $name]);
    }
}