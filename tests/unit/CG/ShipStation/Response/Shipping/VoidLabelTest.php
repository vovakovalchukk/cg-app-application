<?php
namespace CG\ShipStation\Test\Response\Shipping;

use CG\ShipStation\Response\Shipping\VoidLabel;
use PHPUnit_Framework_TestCase;

class VoidLabelTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromJsonWorksWithValidJson()
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../../resources/CG/ShipStation/Response/Shipping/VoidLabelTest/raw_response.json');
        /** @var VoidLabel $response */
        $response = VoidLabel::createFromJson($rawJson);

        $this->assertInstanceOf(VoidLabel::class, $response);
        $this->assertTrue($response->isApproved());
        $this->assertEquals('Request for refund submitted.  This label has been voided.', $response->getMessage());
    }

    public function testCreateFromJsonThrowsExceptionWithInvalidJson()
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../../resources/CG/ShipStation/Response/Shipping/VoidLabelTest/bad_json.json');
        $this->setExpectedException(\RuntimeException::class);
        /** @var VoidLabel $response */
        $response = VoidLabel::createFromJson($rawJson);
    }
}
