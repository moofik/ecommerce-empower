<?php


namespace App\Tests\Unit\Service\Api;


use App\Service\Api\Problem\ApiProblem;
use PHPUnit\Framework\TestCase;

class ApiProblemTest extends TestCase
{
    /**
     * @var ApiProblem
     */
    private $apiProblem;

    public function setUp()
    {
        $this->apiProblem = new ApiProblem(400, ApiProblem::TYPE_VALIDATION_ERROR);
    }

    public function testApiProblemConversionToArray()
    {
        $array = $this->apiProblem->toArray();

        $this->assertEquals([
            'status' => 400,
            'type' => 'validation_error',
            'title' => 'There is a validation error'
        ], $array);
    }

    public function testApiProblemSetExtraData()
    {
        $this->apiProblem->set('test_1', 'test');
        $this->apiProblem->set('test_2', 0);
        $array = $this->apiProblem->toArray();

        $this->assertEquals([
            'status' => 400,
            'type' => 'validation_error',
            'title' => 'There is a validation error',
            'test_1' => 'test',
            'test_2' => 0,
        ], $array);
    }

    public function testApiProblemGetters()
    {
        $this->assertEquals(400, $this->apiProblem->getStatusCode());
        $this->assertEquals('There is a validation error', $this->apiProblem->getTitle());
    }

    public function testApiProblemWithoutType()
    {
        $apiProblem = new ApiProblem(404);

        $array = $apiProblem->toArray();

        $this->assertEquals($array, [
            'status' => 404,
            'type' => 'about:blank',
            'title' => 'Not Found'
        ]);
    }

    public function testApiProblemForUnknownType()
    {
        try {
            new ApiProblem(500, 'unknown');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('No title for type unknown', $e->getMessage());
        }
    }
}