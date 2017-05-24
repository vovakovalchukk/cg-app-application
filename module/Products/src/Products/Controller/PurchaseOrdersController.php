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

    public function __construct(
        ViewModelFactory $viewModelFactory
    ) {
        $this->viewModelFactory = $viewModelFactory;
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setVariable('isSidebarVisible', false);
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        return $view;
    }
}
