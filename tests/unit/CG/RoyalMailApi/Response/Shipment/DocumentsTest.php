<?php
namespace CG\RoyalMailApi\Test\Response\Shipment;

use CG\RoyalMailApi\Response\Shipment\Documents as Response;
use PHPUnit\Framework\TestCase;

class DocumentsTest extends TestCase
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
        $rawJson = file_get_contents(__DIR__ . '/../../../../resources/CG/RoyalMailApi/Response/Shipment/Documents/raw_response.json');
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
            'JVBERi0xLjMKJeLjz9MKMSAwIG9iajw8L1Byb2R1Y2VyKGh0bWxkb2MgMS44LjI3IENvcHlyaWdodCAxOTk3LTIwMDYgRWFzeSBTb2Z0d2FyZSBQcm9kdWN0cywgQWxsIFJpZ2h0cyBSZXNlcnZlZC4pL0NyZWF0aW9uRGF0ZShEOjIwMTUwMjA5MTAxNDMyLTAxMDApPj5lbmRvYmoKMiAwIG9iajw8L1R5cGUvRW5jb2RpbmcvRGlmZmVyZW5jZXNbIDMyL3NwYWNlL2V4Y2xhbS9xdW90ZWRibC9udW1iZXJzaWduL2RvbGxhci9wZXJjZW50L2FtcGVyc2FuZC9xdW90ZXNpbmdsZS9wYXJlbmxlZnQvcGFyZW5yaWdodC9hc3Rlcmlzay9wbHVzL2NvbW1hL2h5cGhlbi9wZXJpb2Qvc2xhc2gvemVyby9vbmUvdHdvL3RocmVlL2ZvdXIvZml2ZS9zaXgvc2V2ZW4vZWlnaHQvbmluZS9jb2xvbi9zZW1pY29sb24vbGVzCnRyYWlsZXIKPDwvU2l6ZSAyNi9Sb290IDI1IDAgUi9JbmZvIDEgMCBSL0lEWzw1Nzc0ODI2ZGMxMDI3ZmIzODgwZGMyMDE3YjQ5MGI2OT48NTc3NDgyNmRjMTAyN2ZiMzg4MGRjMjAxN2I0OTBiNjk+XT4+CnN0YXJ0eHJlZgoxNzEzMjUKJSVFT0YK',
            $response->getInternationalDocument()
        );
    }

    protected function thenAnInvalidArgumentExceptionShouldBeThrown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
    }
}