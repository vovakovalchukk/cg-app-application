<?php
namespace Products\Product\ProductFilters;

use CG\Product\ProductFilter\Entity;
use CG\Product\ProductFilter\Filter;
use CG\Product\ProductFilter\Client\Service as ProductFiltersClientService;
use CG\Stdlib\Exception\Runtime\NotFound;
use PHPUnit\Framework\TestCase;
use Products\Product\ProductFilters\Service as ProductFiltersService;
use Zend\Di\Di;

class ServiceTest extends TestCase
{
    protected $service;
    protected $clientService;
    protected $filter;

    protected function setUp()
    {
        parent::setUp();
        $di = $this->createMock(Di::class);
        $this->clientService = $this->createMock(ProductFiltersClientService::class);

        $this->service = $this->getMockBuilder(ProductFiltersService::class)
            ->setConstructorArgs([$di, $this->clientService])
            ->setMethods(['newProductFilterFilter', 'getProductFiltersService', 'fetchProductFilterByFilter', 'newProductFilter'])
            ->getMock();
        $this->service->expects($this->any())
            ->method('getProductFiltersService')
            ->willReturn($this->clientService);
        $this->filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSaveNoSavedFilters()
    {
        $this->service->expects($this->once())
            ->method('newProductFilterFilter')
            ->willReturn($this->filter);
        $this->service->expects($this->once())
            ->method('fetchProductFilterByFilter')
            ->willThrowException(new NotFound('not found'));

        $productFilter = $this->getMockBuilder(Entity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->service->expects($this->once())
            ->method('newProductFilter')
            ->willReturn($productFilter);

        $this->clientService->expects($this->once())
            ->method('save')
            ->with($productFilter);

        $this->service->save(['filter' => 'data']);
    }

    public function testSaveUserExisting()
    {
        $this->service->expects($this->once())
            ->method('newProductFilterFilter')
            ->willReturn($this->filter);
        $productFilter = $this->getMockBuilder(Entity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productFilter->expects($this->once())
            ->method('getUserId')
            ->willReturn(42);
        $this->service->expects($this->once())
            ->method('fetchProductFilterByFilter')
            ->willReturn($productFilter);

        $this->service->expects($this->never())
            ->method('newProductFilter')
            ->willReturn($productFilter);

        $productFilter->expects($this->once())
            ->method('setFilters');
        $this->clientService->expects($this->once())
            ->method('save')
            ->with($productFilter);

        $this->service->save(['userId' => '42', 'filters' => '{"sort": [{"column": "name", "direction": "asc"}]}']);
    }

    public function testSaveUserExistingOu()
    {
        $this->service->expects($this->once())
            ->method('newProductFilterFilter')
            ->willReturn($this->filter);
        $productFilter = $this->getMockBuilder(Entity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productFilter->expects($this->once())
            ->method('getUserId')
            ->willReturn(null);
        $this->service->expects($this->once())
            ->method('fetchProductFilterByFilter')
            ->willReturn($productFilter);

        $this->service->expects($this->once())
            ->method('newProductFilter')
            ->willReturn($productFilter);

        $productFilter->expects($this->never())
            ->method('setFilters');
        $this->clientService->expects($this->once())
            ->method('save')
            ->with($productFilter);

        $this->service->save(['userId' => '42', 'filters' => '{"sort": [{"column": "name", "direction": "asc"}]}']);
    }

    public function testSaveOu()
    {
        $this->service->expects($this->once())
            ->method('newProductFilterFilter')
            ->willReturn($this->filter);
        $productFilter = $this->getMockBuilder(Entity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productFilter->expects($this->never())
            ->method('getUserId');
        $this->service->expects($this->once())
            ->method('fetchProductFilterByFilter')
            ->willReturn($productFilter);

        $this->service->expects($this->never())
            ->method('newProductFilter')
            ->willReturn($productFilter);

        $productFilter->expects($this->once())
            ->method('setFilters');
        $this->clientService->expects($this->once())
            ->method('save')
            ->with($productFilter);

        $this->service->save(['userId' => null, 'organisationUnitId' => 42, 'filters' => '{"sort": [{"column": "name", "direction": "asc"}]}']);
    }
}
