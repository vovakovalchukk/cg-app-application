<?php
namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

use CG_UI\View\Prototyper\JsonModelFactory;

class PurchaseOrdersJsonController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_LIST = 'AJAX List';
    const ROUTE_COMPLETE = 'AJAX Complete';
    const ROUTE_DOWNLOAD = 'AJAX Download';
    const ROUTE_DELETE = 'AJAX Delete';
    const ROUTE_SAVE = 'AJAX Save';
    const ROUTE_CREATE = 'AJAX Create';

    protected $jsonModelFactory;

    public function __construct(
        JsonModelFactory $jsonModelFactory
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function createAction()
    {
        $number = $this->params()->fromPost('number');
        $products = $this->params()->fromPost('products');

        $id = 2123;
        $success = true;
        return $this->jsonModelFactory->newInstance([
            'success' => $success,
            'id' => $id,
        ]);
    }

    public function saveAction()
    {
        $id = $this->params()->fromPost('id');
        $number = $this->params()->fromPost('number');
        $products = json_decode($this->params()->fromPost('products'));

        $success = true;
        return $this->jsonModelFactory->newInstance([
            'success' => $success,
        ]);
    }

    public function deleteAction()
    {
        $id = $this->params()->fromPost('id');
        $success = true;
        return $this->jsonModelFactory->newInstance([
            'success' => $success
        ]);
    }

    public function downloadAction()
    {
        $id = $this->params()->fromPost('id');
        $success = true;
        return $this->jsonModelFactory->newInstance([
            'success' => $success
        ]);
    }

    public function completeAction()
    {
        $id = $this->params()->fromPost('id');
        $success = true;
        return $this->jsonModelFactory->newInstance([
            'success' => $success
        ]);
    }

    public function listAction()
    {
        $records = [
            [
                'status' => 'Complete',
                'date' => '2017-04-28 13:35:07',
                'number' => '1 Jeans',
                'id' => '4',
            ],[
                'status' => 'In Progress',
                'date' => '2017-04-08 11:35:07',
                'number' => '2 Jeans',
                'id' => '23',
            ]
        ];
        return $this->jsonModelFactory->newInstance([
            'list' => $records,
        ]);
    }
}
