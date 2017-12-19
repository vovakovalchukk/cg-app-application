<?php
namespace CG\ShipStation\Test\Response\Shipping;

use CG\ShipStation\Response\Shipping\Shipments;
use PHPUnit_Framework_TestCase;

class ShipmentsTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromJsonWorksWithValidJson()
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../../resources/CG/ShipStation/Response/Shipping/ShipmentsTest/raw_response.json');
        /** @var Shipments $response */
        $response = Shipments::createFromJson($rawJson);

        $this->assertInstanceOf(Shipments::class, $response);
        $this->assertFalse($response->hasErrors());
        $this->assertInternalType('array', $response->getShipments());
        $this->assertCount(1, $response->getShipments());
        $shipments = $response->getShipments();
        $shipment = array_shift($shipments);
        $this->assertEquals('se-2126954', $shipment->getShipmentId());
    }

    public function testCreateFromJsonThrowsExceptionWithInvalidJson()
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../../resources/CG/ShipStation/Response/Shipping/ShipmentsTest/bad_json.json');
        $this->setExpectedException(\RuntimeException::class);
        /** @var Shipments $response */
        $response = Shipments::createFromJson($rawJson);
    }
}