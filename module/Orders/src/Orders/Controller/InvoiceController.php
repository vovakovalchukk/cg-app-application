<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Orders\Order\Invoice\Service;
use Orders\Order\Invoice\Response;

class InvoiceController extends AbstractActionController
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->setService($service);
    }

    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return Response
     */
    public function generateAction()
    {
        $orderIds = $this->params()->fromPost('orders', []);
        if (!is_array($orderIds) || empty($orderIds)) {
            return $this->redirect()->toRoute('Orders');
        }
        return $this->getService()->getResponseFromOrderIds($orderIds);
    }
}