<?php
namespace Products\Product\ProductSort;

use CG\Product\ProductSort\Entity;
use CG\Product\ProductSort\Filter;
use CG\Product\ProductSort\Client\Service as ProductSortClientService;
use CG\Product\ProductSort\Mapper;
use CG\Stdlib\Exception\Runtime\NotFound;
use PHPUnit\Framework\TestCase;
use Products\Product\ProductSort\Service as ProductSortService;

class ServiceTest extends TestCase
{
    protected $service;
    protected $clientService;
    protected $filter;

    protected function setUp()
    {
        parent::setUp();
        $this->clientService = $this->createMock(ProductSortClientService::class);

        $this->service = $this->getMockBuilder(ProductSortService::class)
            ->setConstructorArgs([$this->clientService, $this->createMock(Mapper::class)])
            ->setMethods(['newProductSortFilter', 'getProductSortService', 'fetchProductSortByFilter', 'newProductSort'])
            ->getMock();
        $this->service->expects($this->any())
            ->method('getProductSortService')
            ->willReturn($this->clientService);
        $this->filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSaveNoSavedFilters()
    {
        $this->service->expects($this->once())
            ->method('newProductSortFilter')
            ->willReturn($this->filter);
        $this->service->expects($this->once())
            ->method('fetchProductSortByFilter')
            ->willThrowException(new NotFound('not found'));

        $productSort = $this->getMockBuilder(Entity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->service->expects($this->once())
            ->method('newProductSort')
            ->willReturn($productSort);

        $this->clientService->expects($this->once())
            ->method('save')
            ->with($productSort);

        $this->service->save(['data' => 'data']);
    }

    public function testSaveUserExisting()
    {
        $this->service->expects($this->once())
            ->method('newProductSortFilter')
            ->willReturn($this->filter);
        $productSort = $this->getMockBuilder(Entity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productSort->expects($this->once())
            ->method('getUserId')
            ->willReturn(42);
        $this->service->expects($this->once())
            ->method('fetchProductSortByFilter')
            ->willReturn($productSort);

        $this->service->expects($this->never())
            ->method('newProductSort')
            ->willReturn($productSort);

        $productSort->expects($this->once())
            ->method('setData');
        $this->clientService->expects($this->once())
            ->method('save')
            ->with($productSort);

        $this->service->save(['userId' => '42', 'data' => '{"sort": [{"column": "name", "direction": "asc"}]}']);
    }

    public function testSaveUserExistingOu()
    {
        $this->service->expects($this->once())
            ->method('newProductSortFilter')
            ->willReturn($this->filter);
        $productSort = $this->getMockBuilder(Entity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productSort->expects($this->once())
            ->method('getUserId')
            ->willReturn(null);
        $this->service->expects($this->once())
            ->method('fetchProductSortByFilter')
            ->willReturn($productSort);

        $this->service->expects($this->once())
            ->method('newProductSort')
            ->willReturn($productSort);

        $productSort->expects($this->never())
            ->method('setData');
        $this->clientService->expects($this->once())
            ->method('save')
            ->with($productSort);

        $this->service->save(['userId' => '42', 'data' => '{"sort": [{"column": "name", "direction": "asc"}]}']);
    }

    public function testSaveOu()
    {
        $this->service->expects($this->once())
            ->method('newProductSortFilter')
            ->willReturn($this->filter);
        $productSort = $this->getMockBuilder(Entity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productSort->expects($this->never())
            ->method('getUserId');
        $this->service->expects($this->once())
            ->method('fetchProductSortByFilter')
            ->willReturn($productSort);

        $this->service->expects($this->never())
            ->method('newProductSort')
            ->willReturn($productSort);

        $productSort->expects($this->once())
            ->method('setData');
        $this->clientService->expects($this->once())
            ->method('save')
            ->with($productSort);

        $this->service->save(['userId' => null, 'organisationUnitId' => 42, 'data' => '{"sort": [{"column": "name", "direction": "asc"}]}']);
    }
}
