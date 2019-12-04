<?php
namespace Orders\Order\TableService;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG_UI\View\Filters\SelectOptionsInterface;
use CG\Supplier\Service as SupplierService;
use CG\Supplier\Entity as Supplier;
use CG\Supplier\Filter as SupplierFilter;
use CG\Supplier\Collection as Suppliers;

class OrdersTableSuppliers implements SelectOptionsInterface
{
    /** @var SupplierService */
    protected $supplierService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(SupplierService $supplierService, ActiveUserInterface $activeUserContainer)
    {
        $this->supplierService = $supplierService;
        $this->activeUserContainer = $activeUserContainer;
    }

    public function getSelectOptions()
    {
        return $this->formatSuppliersAsArray(
            $this->fetchSuppliersForActiveUser()
        );
    }

    protected function fetchSuppliersForActiveUser(): Suppliers
    {
        try {
            return $this->supplierService->fetchCollectionByFilter($this->buildFilter());
        } catch (NotFound $exception) {
            return new Suppliers(Supplier::class, __METHOD__);
        }
    }

    protected function buildFilter(): SupplierFilter
    {
        return new SupplierFilter(
            'all',
            1,
            [],
            [$this->activeUserContainer->getActiveUserRootOrganisationUnitId()]
        );
    }

    protected function formatSuppliersAsArray(Suppliers $suppliers): array
    {
        $suppliersArray = [];
        /** @var Supplier $supplier */
        foreach ($suppliers as $supplier) {
            $suppliersArray[$supplier->getId()] = $supplier->getName();
        }

        return $suppliersArray;
    }
}
