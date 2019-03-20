<?php
namespace CG\RoyalMailApi\Test\Response\Shipment;

use CG\RoyalMailApi\Response\Shipment\Create as Response;
use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
    public function testMappingFromJsonResponseWorks()
    {
        $json = $this->givenValidResponseJson();
        $response = $this->whenTheResponseIsMapped($json);
        $this->thenTheResponseDataShouldBeSuccessfullyParsed($response);
    }

    public function testMappingFromJsonWithMissingShipmentItemsThrowsException()
    {
        $json = $this->givenInvalidResponseJson();
        $this->thenAnInvalidArgumentExceptionShouldBeThrown();
        $this->whenTheResponseIsMapped($json);
    }

    protected function givenValidResponseJson(): \stdClass
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../../resources/CG/RoyalMailApi/Response/Shipment/Create/raw_response.json');
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
        $this->assertCount(1, $response->getShipmentItems());
        $this->assertEquals('HY188980152GB', $response->getShipmentItems()[0]->getShipmentNumber());
        $this->assertEquals(
            'JVBERi0xLjYKJeTjz9IKMSAwIG9iagpbL1BERi9JbWFnZUIvSW1hZ2VDL0ltYWdlSS9UZXh0XQplbmRvYmoKNCAwIG9iago8PC9MZW5ndGggNSAwIFIKL0ZpbHRlci9GbGF0ZURlY29kZQo+PgpzdHJlYW0KeJwDAAAAAAEKZW5kc3RyZWFtCmVuZG9iago1IDAgb2JqCjgKZW5kb2JqCjYgMCBvYmoKPDwv+fr/xAAfAQADAQEBAQEBAQEBAAAAAAAAAQIDBAUGBwgJCgv/xAC1EQACAQIEBAMEBwUEBAABAncAAQIDEQQFITEGEkFRB2FxEyIygQgUQpGhscEJIzNS8BVictEKFiQ04SXxFxgZGiYnKCkqNTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqCg4SFhoeIiYqSk5SVlpeYmZqio6SlpqeoMCBSCi9JbmZvIDMyIDAgUgovSURbPDZDM0VCNEREOEE2OTNEMTVDQUE4NkRCODJCNTc2MTIzPjw2QzNFQjRERDhBNjkzRDE1Q0FBODZEQjgyQjU3NjEyMz5dCj4+CnN0YXJ0eHJlZgoxMzI1OTYKJSVFT0YK',
            $response->getShipmentItems()[0]->getLabel()
        );
    }

    protected function thenAnInvalidArgumentExceptionShouldBeThrown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
    }
}