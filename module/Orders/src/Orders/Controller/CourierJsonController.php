<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class CourierJsonController extends AbstractActionController
{
    const ROUTE_REVIEW_LIST = 'Review List';
    const ROUTE_REVIEW_LIST_URI = '/ajax';

    protected $jsonModelFactory;

    public function __construct(JsonModelFactory $jsonModelFactory)
    {
        $this->setJsonModelFactory($jsonModelFactory);
    }

    public function reviewListAction()
    {
        $data = $this->getDefaultJsonData();
        $orderIds = $this->params('order', []);

        // TODO: populate $data['Records']

        return $this->jsonModelFactory->newInstance($data);
    }

    protected function getDefaultJsonData()
    {
        return [
            'iTotalRecords' => 0,
            'iTotalDisplayRecords' => 0,
            'sEcho' => (int) $this->params()->fromPost('sEcho'),
            'Records' => [],
        ];
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }
}
