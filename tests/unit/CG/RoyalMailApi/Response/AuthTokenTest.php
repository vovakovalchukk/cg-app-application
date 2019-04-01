<?php
namespace CG\RoyalMailApi\Test\Response;

use CG\RoyalMailApi\Response\AuthToken as Response;
use PHPUnit\Framework\TestCase;

class AuthTokenTest extends TestCase
{
    public function testMappingFromJsonResponseWorks()
    {
        $json = $this->givenValidResponseJson();
        $response = $this->whenTheResponseIsMapped($json);
        $this->thenTheResponseDataShouldBeSuccessfullyParsed($response);
    }

    public function testMappingFromJsonWithMissingTokenThrowsException()
    {
        $json = $this->givenInvalidResponseJson();
        $this->thenAnInvalidArgumentExceptionShouldBeThrown();
        $this->whenTheResponseIsMapped($json);
    }

    protected function givenValidResponseJson(): \stdClass
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../resources/CG/RoyalMailApi/Response/AuthToken/raw_response.json');
        return json_decode($rawJson);
    }

    protected function givenInvalidResponseJson(): \stdClass
    {
        $rawJson = '{}';
        return json_decode($rawJson);
    }

    protected function whenTheResponseIsMapped(\stdClass $jsonObject): Response
    {
        return Response::fromJson($jsonObject);
    }

    protected function thenTheResponseDataShouldBeSuccessfullyParsed(Response $response): void
    {
        $this->assertEquals(
            'eyJhbGciOiJIUzI1NiJ9.eyJleHAiOjE1MjU4NzQ3MjQsImlhdCI6MTUyNTg2MDMyNCwidXNlcklkIjoiSE9VTlNMT1cxIiwicGFzc3dvcmQiOiJyWWJHbUZqb3kzNU85M2xia01zazFwbmVpSVE9In0.D662-SDHQxP1ZqUOc9JHmOIW96ICV0Z-Co8eYRZiSwU',
            $response->getAuthToken()->getToken()
        );
    }

    protected function thenAnInvalidArgumentExceptionShouldBeThrown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
    }
}