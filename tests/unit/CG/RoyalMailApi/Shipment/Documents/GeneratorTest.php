<?php
namespace CG\RoyalMailApi\Test\Shipment\Documents;

use CG\CourierAdapter\Provider\Implementation\Package\Content;
use CG\ExchangeRate\Service as ExchangeRateService;
use CG\RoyalMailApi\Client;
use CG\RoyalMailApi\Client\Factory as ClientFactory;
use CG\RoyalMailApi\Request\Shipment\Documents as Request;
use CG\RoyalMailApi\Response\Shipment\Documents as Response;
use CG\RoyalMailApi\Response\Shipment\Completed\Item as ShipmentItem;
use CG\RoyalMailApi\Shipment\Documents\Generator;
use CG\RoyalMailApi\Test\MockAccountTrait;
use CG\RoyalMailApi\Test\MockDeliveryAddressTrait;
use CG\RoyalMailApi\Test\MockPackageTrait;
use CG\RoyalMailApi\Test\MockShipmentTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class GeneratorTest extends TestCase
{
    use MockAccountTrait;
    use MockPackageTrait;
    use MockDeliveryAddressTrait;
    use MockShipmentTrait;

    /** @var Generator */
    protected $generator;
    /** @var MockObject */
    protected $client;
    /** @var MockObject */
    protected $exchangeRateService;
    /** @var Request */
    protected $request;

    public function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $clientFactory = $this->getMockBuilder(ClientFactory::class)->disableOriginalConstructor()->getMock();
        $clientFactory->expects($this->any())
            ->method('__invoke')
            ->willReturn($this->client);
        $this->exchangeRateService = $this->getMockBuilder(ExchangeRateService::class)->disableOriginalConstructor()->getMock();

        $this->generator = new Generator($clientFactory, $this->exchangeRateService);
    }

    public function testLowValuePackageGeneratesCN22()
    {
        $shipment = $this->givenAShipmentOfALowValuePackage();
        $request = $this->whenTheDocumentsRequestIsMade($shipment);
        $this->thenACN22DocumentShouldBeRequested($request);
    }

    public function testHighValuePackageGeneratesCN23()
    {
        $shipment = $this->givenAShipmentOfAHighValuePackage();
        $request = $this->whenTheDocumentsRequestIsMade($shipment);
        $this->thenACN23DocumentShouldBeRequested($request);
    }

    public function testPackageInDifferentCurrencyGetsCorrectDocumentGenerated()
    {
        $shipment = $this->givenAShipmentOfAHighValuePackageInEuroes();
        $request = $this->whenTheDocumentsRequestIsMade($shipment);
        $this->thenACN23DocumentShouldBeRequested($request);
    }

    protected function givenAShipmentOfALowValuePackage(): MockObject
    {
        $shipment = $this->getMockInternationalShipment();
        $package = $shipment->getPackages()[0];
        $package->expects($this->any())
            ->method('getContents')
            ->willReturn([$this->getMockPackageContent(1, 1, 'GBP')]);
        return $shipment;
    }

    protected function givenAShipmentOfAHighValuePackage(): MockObject
    {
        $shipment = $this->getMockInternationalShipment();
        $package = $shipment->getPackages()[0];
        $package->expects($this->any())
            ->method('getContents')
            ->willReturn([$this->getMockPackageContent(1, Generator::CN22_MAX_VALUE_GBP + 1, 'GBP')]);
        return $shipment;
    }

    protected function givenAShipmentOfAHighValuePackageInEuroes(): MockObject
    {
        $shipment = $this->getMockInternationalShipment();
        $package = $shipment->getPackages()[0];
        $package->expects($this->any())
            ->method('getContents')
            ->willReturn([$this->getMockPackageContent(1, 312, 'EUR')]);

        $this->exchangeRateService->expects($this->any())
            ->method('convertAmount')
            ->willReturn(Generator::CN22_MAX_VALUE_GBP + 1);

        return $shipment;
    }

    protected function whenTheDocumentsRequestIsMade(MockObject $shipment): Request
    {
        $rawJson = file_get_contents(__DIR__ . '/../../../../resources/CG/RoyalMailApi/Response/Shipment/Documents/raw_response.json');
        $response = Response::fromJson(json_decode($rawJson));
        $this->client->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function ($request) use ($response)
            {
                $this->request = $request;
                return $response;
            }));

        ($this->generator)($this->getMockShipmentItem(), $shipment);
        return $this->request;
    }

    protected function thenACN22DocumentShouldBeRequested(Request $request): void
    {
        $this->assertEquals($request->getDocumentName(), Request::DOCUMENT_CN22);
    }

    protected function thenACN23DocumentShouldBeRequested(Request $request): void
    {
        $this->assertEquals($request->getDocumentName(), Request::DOCUMENT_CN23);
    }

    protected function getMockPackageContent(int $quantity = 1, float $value = 0, string $currency = 'GBP'): MockObject
    {
        $content = $this->getMockBuilder(Content::class)->disableOriginalConstructor()->getMock();
        $content->expects($this->any())
            ->method('getQuantity')
            ->willReturn($quantity);
        $content->expects($this->any())
            ->method('getUnitValue')
            ->willReturn($value);
        $content->expects($this->any())
            ->method('getUnitCurrency')
            ->willReturn($currency);
        return $content;
    }

    protected function getMockShipmentItem(): MockObject
    {
        return $this->getMockBuilder(ShipmentItem::class)->disableOriginalConstructor()->getMock();
    }
}