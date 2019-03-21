<?php
namespace CG\RoyalMailApi\Test\Shipment;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\DocumentInterface;
use CG\RoyalMailApi\Client;
use CG\RoyalMailApi\Client\Factory as ClientFactory;
use CG\RoyalMailApi\Manifest;
use CG\RoyalMailApi\ManifestService;
use CG\RoyalMailApi\Response\Manifest\Create as CreateManifestResponse;
use CG\RoyalMailApi\Response\Manifest\PrintManifest as PrintManifestResponse;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ManifestServiceTest extends TestCase
{
    /** @var ManifestService */
    protected $manifestService;
    /** @var MockObject */
    protected $client;

    public function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        /** @var ClientFactory|MockObject $clientFactory */
        $clientFactory = $this->getMockBuilder(ClientFactory::class)->disableOriginalConstructor()->getMock();
        $clientFactory->expects($this->any())
            ->method('__invoke')
            ->willReturn($this->client);

        $this->manifestService = new ManifestService($clientFactory);
    }

    public function testManifestCreationSuccess()
    {
        $response = CreateManifestResponse::fromJson($this->buildValidCreateManifestJsonResponse());

        $this->client->expects($this->any())
            ->method('send')
            ->willReturn($response);

        $manifest = $this->manifestService->createManifest($this->createAccount());
        $this->assertManifestIsValid($manifest);
    }

    public function testManifestCreationAndPrintFirstTry()
    {
        $createManifestResponse = CreateManifestResponse::fromJson($this->buildCreateManifestJsonResponseWithoutManifest());
        $printManifestResponse = PrintManifestResponse::fromJson($this->buildValidPrintManifestJsonResponse());

        $this->client->expects($this->at(0))
            ->method('send')
            ->willReturn($createManifestResponse);

        $this->client->expects($this->at(1))
            ->method('send')
            ->willReturn($printManifestResponse);

        $manifest = $this->manifestService->createManifest($this->createAccount());
        $this->assertManifestIsValid($manifest);
    }

    public function testManifestCreationAndPrintSecondTry()
    {
        $createManifestResponse = CreateManifestResponse::fromJson($this->buildCreateManifestJsonResponseWithoutManifest());

        $printManifestResponse = PrintManifestResponse::fromJson($this->buildValidPrintManifestJsonResponse());
        $printManifestResponseWithoutManifest = new PrintManifestResponse();

        $this->client->expects($this->at(0))
            ->method('send')
            ->willReturn($createManifestResponse);

        $this->client->expects($this->at(1))
            ->method('send')
            ->willReturn($printManifestResponseWithoutManifest);

        $this->client->expects($this->at(2))
            ->method('send')
            ->willReturn($printManifestResponse);

        $manifest = $this->manifestService->createManifest($this->createAccount());
        $this->assertManifestIsValid($manifest);
    }

    public function testManifestCreationAndPrintTimesOut()
    {

    }

    protected function createAccount(): Account
    {
        return new Account([]);
    }

    protected function buildValidCreateManifestJsonResponse(): \stdClass
    {
        $rawJson = file_get_contents(__DIR__ . '/../../resources/CG/RoyalMailApi/Response/Manifest/Create/raw_response.json');
        return json_decode($rawJson);
    }

    protected function buildCreateManifestJsonResponseWithoutManifest(): \stdClass
    {
        $rawJson = file_get_contents(__DIR__ . '/../../resources/CG/RoyalMailApi/Response/Manifest/Create/raw_response_without_manifest.json');
        return json_decode($rawJson);
    }

    protected function buildValidPrintManifestJsonResponse(): \stdClass
    {
        $rawJson = file_get_contents(__DIR__ . '/../../resources/CG/RoyalMailApi/Response/Manifest/CreateImage/raw_response.json');
        return json_decode($rawJson);
    }

    protected function assertManifestIsValid(Manifest $manifest): void
    {
        $this->assertEquals(81, $manifest->getReference());
        $this->assertInstanceOf(Account::class, $manifest->getAccount());
        $this->assertEquals(DocumentInterface::TYPE_PDF, $manifest->getType());
        $this->assertEquals('JVBERi0xLjMKJeLjz9MKMSAwIG9iajw8L1Byb2R1Y2VyKGh0bWxkb2MgMS44LjI3IENvcHlyaWdo dCAxOTk3LTIwMDYgRWFzeSBTb2Z0d2FyZSBQcm9kdWN0cywgQWxsIFJpZ2h0cyBSZXNlcnZlZC4p L0NyZWF0aW9uRGF0ZShEOjIwMTUwMjA2MTUwNTEyLTAxMDApPj5lbmRvYmoKMiAwIG9iajw8L1R5 cGUvRW5jb2RpbmcvRGlmZmVyZW5jZXNbIDMyL3NwYWNlL2V4Y2xhbS9xdW90ZWRibC9udW1iZXJz aWduL2RvbGxhci9wZXJjZW50L2FtcGVyc2FuZC9xdW90ZXNpbmdsZS9wYXJlbmxlZnQvcGFyZW5y aWdodC9hc3Rlcmlzay9wbHVzL2NvbW1hL2h5cGhlbi9wZXJpb2Qvc2xhc2gvemVyby9vbmUvdHdv L3RocmVlL2ZvdXIvZml2ZS9zaXgvc2V2ZW4vZWlnaHQvbmluZS9jb2xvbi9zZW1pY29sb24vbGVz cy9lcXVhbC9ncmVhdGVyL3F1ZXN0aW9uL2F0L0EvQi9DL0QvRS9GL0cvSC9JL0ovSy9ML00vTi9P L1AvUS9SL1MvVC9VL1YvVy9YL1kvWi9icmFja2V0bGVmdC9iYWNrc2xhc2gvYnJhY2tldHJpZ2h0 biAKMDAwMDEyMjYwMyAwMDAwMCBuIAowMDAwMTIyNjU1IDAwMDAwIG4gCjAwMDAxMjI3MzYgMDAw MDAgbiAKdHJhaWxlcgo8PC9TaXplIDI3L1Jvb3QgMjYgMCBSL0luZm8gMSAwIFIvSURbPDkzNjgx OThmZWM3ODA1ZjgxYmM4ZDgzYTI3MTg3NmQ2Pjw5MzY4MTk4ZmVjNzgwNWY4MWJjOGQ4M2EyNzE4 NzZkNj5dPj4Kc3RhcnR4cmVmCjEyMjkxNwolJUVPRgo=', $manifest->getData());
    }
}
