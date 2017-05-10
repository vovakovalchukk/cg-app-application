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

    protected $jsonModelFactory;

    public function __construct(
        JsonModelFactory $jsonModelFactory
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function listAction()
    {
        $records = [
            [
                'status' => 'In Progress',
                'date' => '2017-04-28 13:35:07',
                'number' => '1 Jeans',
            ],[
                'status' => 'In Progress',
                'date' => '2017-04-08 11:35:07',
                'number' => '2 Jeans',
            ],[
                'status' => 'Complete',
                'date' => '2017-03-28 13:35:07',
                'number' => 'Some Jeans',
            ],[
                'status' => 'Complete',
                'date' => '2017-03-21 13:35:07',
                'number' => 'More Jeans',
            ],[
                'status' => 'Complete',
                'date' => '2017-03-21 13:35:07',
                'number' => 'Even More Jeans',
            ]
        ];
        return $this->jsonModelFactory->newInstance([
            'list' => $records,
        ]);
    }
}
