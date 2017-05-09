<?php
namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

use CG_UI\View\DataTable;
use CG_UI\View\Prototyper\ViewModelFactory;
use Products\Module;

class PurchaseOrdersController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_INDEX = 'purchaseOrders';
    const ROUTE_INDEX_URL = '/purchaseOrders';

    protected $viewModelFactory;
    protected $purchaseOrdersList;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        DataTable $purchaseOrdersList
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->purchaseOrdersList = $purchaseOrdersList;
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setVariable('isHeaderBarVisible', false);

        $view->addChild($this->getPurchaseOrdersList(), 'purchaseOrdersList');
        return $view;
    }

    protected function getPurchaseOrdersList()
    {
        $purchaseOrdersList = $this->purchaseOrdersList;
        $settings = $purchaseOrdersList->getVariable('settings');
        $settings->setSource(
            $this->url()->fromRoute(Module::ROUTE . '/' . static::ROUTE_INDEX . '/' . PurchaseOrdersJsonController::ROUTE_DATATABLE)
        );
        $settings->setTemplateUrlMap($this->mustacheTemplateMap('listingList'));
        return $purchaseOrdersList;
    }
}
