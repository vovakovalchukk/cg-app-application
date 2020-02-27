<?php
namespace Products\Controller;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Products\Product\Supplier\Service as SupplierService;

class PurchaseOrdersController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_INDEX = 'purchaseOrders';
    const ROUTE_INDEX_URL = '/purchaseOrders';

    protected $viewModelFactory;

    protected $supplierService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        SupplierService $supplierService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->supplierService = $supplierService;
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setVariable('isSidebarVisible', false);
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        $view->setVariable('supplierOptions', $this->supplierService->getSupplierOptions());
        return $view;
    }
}
