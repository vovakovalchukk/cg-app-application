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

    protected function createElement(array $config)
    {
        $class = 'CG\\Template\\Element\\' . ucfirst($config['type']);
        return $this->getService()->getDi()->get($class, $config);
    }

    /**
     * @return Response
     */
    public function generatePreviewAction()
    {
        $filter = $this->getService()->getDi()->get('CG\\Order\\Service\\Filter', [
            'limit' => 1,
            'organisationUnitId' => $this->getService()->getOrderService()->getActiveUser()->getOuList()
        ]);
        $orders = $this->getService()->getOrderService()->getOrders($filter);
        $elements = [];

        $templateConfig = json_decode($this->params()->fromPost('template'), true);
        foreach ($templateConfig['elements'] as $element) {
            $elements[] = $this->createElement($element);
        }
        $templateConfig['elements'] = $elements;

        $template = $this->getService()->getTemplateFactory()->getTemplateForOrderEntity($templateConfig);
        return $this->getService()->getResponseFromOrderCollection($orders, $template);
    }
}