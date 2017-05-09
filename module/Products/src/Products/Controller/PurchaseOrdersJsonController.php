<?php
namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

use CG_UI\View\Prototyper\JsonModelFactory;

class PurchaseOrdersJsonController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_DATATABLE = 'AJAX Datatable';

    protected $jsonModelFactory;

    public function __construct(
        JsonModelFactory $jsonModelFactory
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function datatableAction()
    {
        return
            [
                'iTotalRecords' => 0,
                'iTotalDisplayRecords' => 0,
                'sEcho' => (int) $this->params()->fromPost('sEcho'),
                'Records' => [],
            ];
    }
}
