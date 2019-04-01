<?php
namespace CG\RoyalMailApi\Test\Shipment;

use CG\CourierAdapter\Provider\Implementation\Label;
use CG\RoyalMailApi\Client;
use CG\RoyalMailApi\Client\Factory as ClientFactory;
use CG\RoyalMailApi\Request\Shipment\Create as Request;
use CG\RoyalMailApi\Request\Shipment\Create\Domestic as DomesticRequest;
use CG\RoyalMailApi\Request\Shipment\Create\International as InternationalRequest;
use CG\RoyalMailApi\Response\Shipment\Create as Response;
use CG\RoyalMailApi\Shipment;
use CG\RoyalMailApi\Shipment\Booker;
use CG\RoyalMailApi\Shipment\Documents\Generator as DocumentsGenerator;
use CG\RoyalMailApi\Shipment\Label\Generator as LabelGenerator;
use CG\RoyalMailApi\Test\MockAccountTrait;
use CG\RoyalMailApi\Test\MockDeliveryAddressTrait;
use CG\RoyalMailApi\Test\MockPackageTrait;
use CG\RoyalMailApi\Test\MockShipmentTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class BookerTest extends TestCase
{
    use MockAccountTrait;
    use MockPackageTrait;
    use MockDeliveryAddressTrait;
    use MockShipmentTrait;

    /** @var Booker */
    protected $booker;
    /** @var MockObject */
    protected $client;
    /** @var MockObject */
    protected $labelGenerator;
    /** @var MockObject */
    protected $documentsGenerator;
    /** @var Request */
    protected $request;

    public function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $clientFactory = $this->getMockBuilder(ClientFactory::class)->disableOriginalConstructor()->getMock();
        $clientFactory->expects($this->any())
            ->method('__invoke')
            ->willReturn($this->client);
        $this->labelGenerator = $this->getMockBuilder(LabelGenerator::class)->disableOriginalConstructor()->getMock();
        $this->documentsGenerator = $this->getMockBuilder(DocumentsGenerator::class)->disableOriginalConstructor()->getMock();

        $this->booker = new Booker($clientFactory, $this->labelGenerator, $this->documentsGenerator);
    }

    public function testDomesticShipmentCreatesDomesticRequest()
    {
        $shipment = $this->givenAValidDomesticShipment();
        $request = $this->whenTheShipmentRequestIsMade($shipment);
        $this->thenTheRequestShouldBeForADomesticShipment($request);
    }

    public function testInternationalShipmentCreatesInternationalRequest()
    {
        $shipment = $this->givenAValidInternationalShipment();
        $request = $this->whenTheShipmentRequestIsMade($shipment);
        $this->thenTheRequestShouldBeForAnInternationalShipment($request);
    }

    public function testShipmentUpdatedFromResponse()
    {
        $shipment = $this->givenAValidDomesticShipment();
        $this->whenTheShipmentIsBookedSuccessfully($shipment);
        $this->thenTheShipmentShouldBeUpdatedFromTheResponse($shipment);
    }

    public function testOneDBarcodeUsedAsTrackingNumberWhenPresent()
    {
        $shipment = $this->givenAValidDomesticShipment();
        $this->whenATrackedShipmentIsBookedSuccessfully($shipment);
        $this->thenTheTrackingNumberShouldBeFromTheOneDBarcode($shipment);
    }

    public function testTwoDBarcodeUsedAsTrackingNumberWhenTheresNoOneDBarcode()
    {
        $shipment = $this->givenAValidDomesticShipment();
        $this->whenANonTrackedShipmentIsBookedSuccessfully($shipment);
        $this->thenTheTrackingNumberShouldBeFromTheTwoDBarcode($shipment);
    }

    public function testSuccessfulShipmentWithoutLabelThenFetchesTheLabel()
    {
        $shipment = $this->givenAValidDomesticShipment();
        $this->thenTheLabelShouldBeFetchedSeparately($shipment);
        $this->whenTheShipmentIsBookedSuccessfullyButWithoutALabelReturned($shipment);
    }

    public function testSuccessfulShipmentWithLabelDoesntThenFetchTheLabel()
    {
        $shipment = $this->givenAValidDomesticShipment();
        $this->thenTheLabelShouldNotBeFetchedSeparately($shipment);
        $this->whenTheShipmentIsBookedSuccessfully($shipment);
    }

    public function testInternationalShipmentWithoutLabelThenFetchesTheInternationalDocs()
    {
        $shipment = $this->givenAValidInternationalShipment();
        $this->thenTheInternationalDocsShouldBeFetched($shipment);
        $this->whenTheShipmentIsBookedSuccessfullyButWithoutALabelReturned($shipment);
    }

    protected function givenAValidDomesticShipment(): MockObject
    {
        return $this->getMockDomesticShipment();
    }

    protected function givenAValidInternationalShipment(): MockObject
    {
        return $this->getMockInternationalShipment();
    }

    protected function whenTheShipmentIsBookedSuccessfully(MockObject $shipment): MockObject
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../resources/CG/RoyalMailApi/Response/Shipment/Create/raw_response.json');
        $response = Response::fromJson(json_decode($rawJson));
        return $this->whenTheShipmentIsBooked($shipment, $response);
    }

    protected function whenTheShipmentIsBooked(MockObject $shipment, Response $response): MockObject
    {
        $this->client->expects($this->once())
            ->method('send')
            ->willReturn($response);

        return ($this->booker)($shipment);
    }

    protected function whenATrackedShipmentIsBookedSuccessfully(MockObject $shipment): MockObject
    {
        return $this->whenTheShipmentIsBookedSuccessfully($shipment);
    }

    protected function whenANonTrackedShipmentIsBookedSuccessfully(MockObject $shipment): MockObject
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../resources/CG/RoyalMailApi/Response/Shipment/Create/raw_response_non_tracked.json');
        $response = Response::fromJson(json_decode($rawJson));
        return $this->whenTheShipmentIsBooked($shipment, $response);
    }

    protected function whenTheShipmentRequestIsMade(MockObject $shipment): Request
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../resources/CG/RoyalMailApi/Response/Shipment/Create/raw_response.json');
        $response = Response::fromJson(json_decode($rawJson));
        $this->client->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function ($request) use ($response)
            {
                $this->request = $request;
                return $response;
            }));

        ($this->booker)($shipment);
        return $this->request;
    }

    protected function whenTheShipmentIsBookedSuccessfullyButWithoutALabelReturned(MockObject $shipment)
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../resources/CG/RoyalMailApi/Response/Shipment/Create/raw_response_no_label.json');
        $response = Response::fromJson(json_decode($rawJson));
        return $this->whenTheShipmentIsBooked($shipment, $response);
    }

    protected function thenTheShipmentShouldBeUpdatedFromTheResponse(MockObject $shipment)
    {
        $this->assertEquals('HY188980152GB', $shipment->getPackages()[0]->getTrackingReference());
        $this->assertInstanceOf(Label::class, $shipment->getPackages()[0]->getLabel());
        $this->assertEquals(
            'JVBERi0xLjYKJeTjz9IKMSAwIG9iagpbL1BERi9JbWFnZUIvSW1hZ2VDL0ltYWdlSS9UZXh0XQplbmRvYmoKNCAwIG9iago8PC9MZW5ndGggNSAwIFIKL0ZpbHRlci9GbGF0ZURlY29kZQo+PgpzdHJlYW0KeJwDAAAAAAEKZW5kc3RyZWFtCmVuZG9iago1IDAgb2JqCjgKZW5kb2JqCjYgMCBvYmoKPDwv+fr/xAAfAQADAQEBAQEBAQEBAAAAAAAAAQIDBAUGBwgJCgv/xAC1EQACAQIEBAMEBwUEBAABAncAAQIDEQQFITEGEkFRB2FxEyIygQgUQpGhscEJIzNS8BVictEKFiQ04SXxFxgZGiYnKCkqNTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqCg4SFhoeIiYqSk5SVlpeYmZqio6SlpqeoMCBSCi9JbmZvIDMyIDAgUgovSURbPDZDM0VCNEREOEE2OTNEMTVDQUE4NkRCODJCNTc2MTIzPjw2QzNFQjRERDhBNjkzRDE1Q0FBODZEQjgyQjU3NjEyMz5dCj4+CnN0YXJ0eHJlZgoxMzI1OTYKJSVFT0YK',
            $shipment->getPackages()[0]->getLabel()->getData()
        );
    }

    protected function thenTheTrackingNumberShouldBeFromTheOneDBarcode(MockObject $shipment)
    {
        $this->assertEquals('HY188980152GB', $shipment->getPackages()[0]->getTrackingReference());
    }

    protected function thenTheTrackingNumberShouldBeFromTheTwoDBarcode(MockObject $shipment)
    {
        $this->assertEquals('1000076', $shipment->getPackages()[0]->getTrackingReference());
    }

    protected function thenTheRequestShouldBeForADomesticShipment(Request $request): void
    {
        $this->assertInstanceOf(DomesticRequest::class, $request);
    }

    protected function thenTheRequestShouldBeForAnInternationalShipment(Request $request): void
    {
        $this->assertInstanceOf(InternationalRequest::class, $request);
    }

    protected function thenTheLabelShouldBeFetchedSeparately()
    {
        $this->labelGenerator->expects($this->once())
            ->method('__invoke');
    }

    protected function thenTheLabelShouldNotBeFetchedSeparately()
    {
        $this->labelGenerator->expects($this->never())
            ->method('__invoke');
    }

    protected function thenTheInternationalDocsShouldBeFetched()
    {
        $this->labelGenerator->expects($this->once())
            ->method('__invoke')
            ->willReturn('JVBERi0xLjYKJeTjz9IKMSAwIG9iagpbL1BERi9JbWFnZUIvSW1hZ2VDL0ltYWdlSS9UZXh0XQplbmRvYmoKNCAwIG9iago8PC9MZW5ndGggNSAwIFIKL0ZpbHRlci9GbGF0ZURlY29kZQo');

        $this->documentsGenerator->expects($this->once())
            ->method('__invoke')
            ->willReturn('JVBERi0xLjYKJeTjz9IKMSAwIG9iagpbL1BERi9JbWFnZUIvSW1hZ2VDL0ltYWdlSS9UZXh0XQplbmRvYmoKNCAwIG9iago8PC9MZW5ndGggNSAwIFIKL0ZpbHRlci9GbGF0ZURlY29kZQo');
    }
}