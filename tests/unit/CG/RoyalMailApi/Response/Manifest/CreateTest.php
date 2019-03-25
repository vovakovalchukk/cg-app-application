<?php
namespace CG\RoyalMailApi\Test\Response\Manifest;

use CG\RoyalMailApi\Response\Manifest\Create as CreateResponse;
use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
    public function testCreateFromJsonSuccess()
    {
        $json = $this->givenValidResponseJson();
        $response = $this->whenTheResponseIsMapped($json);
        $this->thenTheResponseDataShouldBeSuccessfullyParsed($response);
    }

    public function testCreateFromJsonFail()
    {
        $json = $this->givenInvalidResponseJson();
        $this->thenAnInvalidArgumentExceptionShouldBeThrown();
        $this->whenTheResponseIsMapped($json);
    }

    protected function givenValidResponseJson(): \stdClass
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../../resources/CG/RoyalMailApi/Response/Manifest/Create/raw_response.json');
        return json_decode($rawJson);
    }

    protected function givenInvalidResponseJson(): \stdClass
    {
        $rawJson = '{}';
        return json_decode($rawJson);
    }

    protected function whenTheResponseIsMapped(\stdClass $jsonObject): CreateResponse
    {
        return CreateResponse::fromJson($jsonObject);
    }

    protected function thenTheResponseDataShouldBeSuccessfullyParsed(CreateResponse $response): void
    {
        $this->assertCount(1, $response->getShipments());
        $this->assertEquals(81, $response->getBatchNumber());
        $this->assertEquals('JVBERi0xLjMKJeLjz9MKMSAwIG9iajw8L1Byb2R1Y2VyKGh0bWxkb2MgMS44LjI3IENvcHlyaWdo dCAxOTk3LTIwMDYgRWFzeSBTb2Z0d2FyZSBQcm9kdWN0cywgQWxsIFJpZ2h0cyBSZXNlcnZlZC4p L0NyZWF0aW9uRGF0ZShEOjIwMTUwMjA2MTUwNTEyLTAxMDApPj5lbmRvYmoKMiAwIG9iajw8L1R5 cGUvRW5jb2RpbmcvRGlmZmVyZW5jZXNbIDMyL3NwYWNlL2V4Y2xhbS9xdW90ZWRibC9udW1iZXJz aWduL2RvbGxhci9wZXJjZW50L2FtcGVyc2FuZC9xdW90ZXNpbmdsZS9wYXJlbmxlZnQvcGFyZW5y aWdodC9hc3Rlcmlzay9wbHVzL2NvbW1hL2h5cGhlbi9wZXJpb2Qvc2xhc2gvemVyby9vbmUvdHdv L3RocmVlL2ZvdXIvZml2ZS9zaXgvc2V2ZW4vZWlnaHQvbmluZS9jb2xvbi9zZW1pY29sb24vbGVz cy9lcXVhbC9ncmVhdGVyL3F1ZXN0aW9uL2F0L0EvQi9DL0QvRS9GL0cvSC9JL0ovSy9ML00vTi9P L1AvUS9SL1MvVC9VL1YvVy9YL1kvWi9icmFja2V0bGVmdC9iYWNrc2xhc2gvYnJhY2tldHJpZ2h0 biAKMDAwMDEyMjYwMyAwMDAwMCBuIAowMDAwMTIyNjU1IDAwMDAwIG4gCjAwMDAxMjI3MzYgMDAw MDAgbiAKdHJhaWxlcgo8PC9TaXplIDI3L1Jvb3QgMjYgMCBSL0luZm8gMSAwIFIvSURbPDkzNjgx OThmZWM3ODA1ZjgxYmM4ZDgzYTI3MTg3NmQ2Pjw5MzY4MTk4ZmVjNzgwNWY4MWJjOGQ4M2EyNzE4 NzZkNj5dPj4Kc3RhcnR4cmVmCjEyMjkxNwolJUVPRgo=', $response->getManifest());
        $this->assertEquals(1, $response->getCount());
    }

    protected function thenAnInvalidArgumentExceptionShouldBeThrown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
    }
}
