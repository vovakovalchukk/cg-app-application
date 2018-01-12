<?php
namespace CG\ShipStation\Test\Response\Shipping;

use CG\ShipStation\Response\Shipping\Label;
use PHPUnit\Framework\TestCase;

class LabelTest extends TestCase
{
    public function testCreateFromJsonWorksWithValidJson()
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../../resources/CG/ShipStation/Response/Shipping/LabelTest/raw_response.json');
        /** @var Label $response */
        $response = Label::createFromJson($rawJson);

        $this->assertInstanceOf(Label::class, $response);
        $this->assertEquals('se-test-2128732', $response->getLabelId());
        $this->assertEquals('pdf', $response->getLabelFormat());
        $this->assertEquals(
            'https://api.shipengine.com/v1/downloads/1/s_Tqsu9euEKub6Acc_9UIg/testlabel-2128732.pdf',
            $response->getLabelDownload()
        );
    }

    public function testCreateFromJsonThrowsExceptionWithInvalidJson()
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../../resources/CG/ShipStation/Response/Shipping/LabelTest/bad_json.json');
        $this->expectException(\RuntimeException::class);
        /** @var Label $response */
        $response = Label::createFromJson($rawJson);
    }
}
