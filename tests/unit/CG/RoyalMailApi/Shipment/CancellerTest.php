<?php
namespace CG\RoyalMailApi\Test\Shipment;

use CG\RoyalMailApi\Client;
use CG\RoyalMailApi\Client\Factory as ClientFactory;
use CG\RoyalMailApi\Response\Shipment\Cancel as Response;
use CG\RoyalMailApi\Shipment\Booker;
use CG\RoyalMailApi\Shipment\Canceller;
use CG\RoyalMailApi\Test\MockAccountTrait;
use CG\RoyalMailApi\Test\MockDeliveryAddressTrait;
use CG\RoyalMailApi\Test\MockPackageTrait;
use CG\RoyalMailApi\Test\MockShipmentTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CancellerTest extends TestCase
{
    use MockAccountTrait;
    use MockPackageTrait;
    use MockDeliveryAddressTrait;
    use MockShipmentTrait;

    /** @var Canceller */
    protected $canceller;
    /** @var MockObject */
    protected $client;

    public function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $clientFactory = $this->getMockBuilder(ClientFactory::class)->disableOriginalConstructor()->getMock();
        $clientFactory->expects($this->any())
            ->method('__invoke')
            ->willReturn($this->client);

        $this->canceller = new Canceller($clientFactory);
    }

    public function testACancelRequestIsMadePerShipmentNumber()
    {
        $shipment = $this->givenAShipmentWithMultipleShipmentNumbers();
        $this->thenACancelRequestShouldBeMadeForEachShipmentNumber($shipment);
        $this->whenTheShipmentIsCancelled($shipment);
    }

    protected function givenAShipmentWithMultipleShipmentNumbers(): MockObject
    {
        $shipment = $this->getMockDomesticShipment();
        $shipment->setCourierReference('TT12346789GB' . Booker::SHIP_NO_SEP . 'TT987654321GB');
        return $shipment;
    }

    protected function thenACancelRequestShouldBeMadeForEachShipmentNumber(MockObject $shipment): void
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../resources/CG/RoyalMailApi/Response/Shipment/Cancel/raw_response.json');
        $response = Response::fromJson(json_decode($rawJson));
        $shipmentNumbers = explode(Booker::SHIP_NO_SEP, $shipment->getCourierReference());
        $this->client->expects($this->exactly(count($shipmentNumbers)))
            ->method('send')
            ->willReturn($response);
    }

    protected function whenTheShipmentIsCancelled(MockObject $shipment): void
    {
        ($this->canceller)($shipment);
    }
}