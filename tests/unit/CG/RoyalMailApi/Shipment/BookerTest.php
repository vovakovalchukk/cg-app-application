<?php
namespace CG\RoyalMailApi\Test\Shipment;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\Address;
use CG\CourierAdapter\Provider\Implementation\Label;
use CG\RoyalMailApi\Client;
use CG\RoyalMailApi\Client\Factory as ClientFactory;
use CG\RoyalMailApi\Request\Shipment\Create as Request;
use CG\RoyalMailApi\Request\Shipment\Create\Domestic as DomesticRequest;
use CG\RoyalMailApi\Request\Shipment\Create\International as InternationalRequest;
use CG\RoyalMailApi\Response\Shipment\Create as Response;
use CG\RoyalMailApi\Shipment;
use CG\RoyalMailApi\Shipment\Booker;
use CG\RoyalMailApi\Shipment\Package;
use CG\RoyalMailApi\Shipment\Label\Generator as LabelGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class BookerTest extends TestCase
{
    /** @var Booker */
    protected $booker;
    /** @var MockObject */
    protected $client;
    /** @var Request */
    protected $request;

    public function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $clientFactory = $this->getMockBuilder(ClientFactory::class)->disableOriginalConstructor()->getMock();
        $clientFactory->expects($this->any())
            ->method('__invoke')
            ->willReturn($this->client);
        $labelGenerator = $this->getMockBuilder(LabelGenerator::class)->disableOriginalConstructor()->getMock();

        $this->booker = new Booker($clientFactory, $labelGenerator);
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

    protected function givenAValidShipment(): MockObject
    {
        $shipment = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            // Dont mock all the methods, requires too much duplication
            ->setMethods(['getAccount', 'getPackages', 'getDeliveryAddress'])
            ->getMock();

        $shipment->expects($this->any())->method('getAccount')->willReturn($this->getMockAccount());
        $shipment->expects($this->any())->method('getPackages')->willReturn([$this->getMockPackage()]);

        return $shipment;
    }

    protected function givenAValidDomesticShipment(): MockObject
    {
        $shipment = $this->givenAValidShipment();
        $shipment->expects($this->any())->method('getDeliveryAddress')->willReturn($this->getMockDeliveryAddress());
        return $shipment;
    }

    protected function givenAValidInternationalShipment(): MockObject
    {
        $shipment = $this->givenAValidShipment();
        $shipment->expects($this->any())->method('getDeliveryAddress')->willReturn($this->getMockDeliveryAddress('FR'));
        return $shipment;
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

    protected function whenTheShipmentRequestIsMade(MockObject $shipment)
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

    protected function getMockAccount(): MockObject
    {
        $account = $this->getMockBuilder(Account::class)
            ->disableOriginalConstructor()
            ->getMock();
        $account->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        return $account;
    }

    protected function getMockPackage(): MockObject
    {
        $package = $this->getMockBuilder(Package::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        return $package;
    }

    protected function getMockDeliveryAddress(?string $countryCode = null): MockObject
    {
        $deliveryAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getISOAlpha2CountryCode'])
            ->getMock();
        $deliveryAddress->expects($this->any())
            ->method('getISOAlpha2CountryCode')
            ->willReturn($countryCode ?? Booker::DOMESTIC_COUNTRY);
        return $deliveryAddress;
    }
}