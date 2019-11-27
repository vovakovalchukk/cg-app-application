<?php
namespace Products\Product\Supplier;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Supplier\Collection as SupplierCollection;
use CG\Supplier\Entity as Supplier;
use CG\Supplier\Filter as SupplierFilter;
use CG\Supplier\Service as SupplierService;
use CG\User\ActiveUserInterface;

class Service
{
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var SupplierService */
    protected $supplierService;

    public function __construct(ActiveUserInterface $activeUserContainer, SupplierService $supplierService)
    {
        $this->activeUserContainer = $activeUserContainer;
        $this->supplierService = $supplierService;
    }

    public function getSupplierOptions(): array
    {
        $suppliers = $this->fetchSuppliersForActiveOu();
        return $this->suppliersToOptions($suppliers);
    }

    protected function fetchSuppliersForActiveOu(): ?SupplierCollection
    {
        try {
            $filter = (new SupplierFilter())
                ->setLimit('all')
                ->setPage(1)
                ->setOrganisationUnitId([$this->activeUserContainer->getActiveUserRootOrganisationUnitId()]);
            return $this->supplierService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return null;
        }
    }

    protected function suppliersToOptions(?SupplierCollection $suppliers): array
    {
        $options = [];
        if ($suppliers == null) {
            return $options;
        }
        /** @var Supplier $supplier */
        foreach ($suppliers as $supplier) {
            $options[$supplier->getId()] = $supplier->getName();
        }
        return $options;
    }
}