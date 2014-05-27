<?php
namespace Orders\Controller;

use CG\Template\PaperPage;
use CG\Template\Element\Mapper as ElementMapper;
use Zend\Mvc\Controller\AbstractActionController;
use Orders\Order\Invoice\Service;
use Orders\Order\Invoice\Response;

class InvoiceController extends AbstractActionController
{
    protected $service;

    public function __construct(Service $service, ElementMapper $elementMapper)
    {
        $this->setService($service)
             ->setElementMapper($elementMapper);
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

    public function setElementMapper(ElementMapper $elementMapper)
    {
        $this->elementMapper = $elementMapper;
        return $this;
    }

    /**
     * @return ElementMapper
     */
    public function getElementMapper()
    {
        return $this->elementMapper;
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
        return $this->getElementMapper()->fromArray($config);

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
            $elements[] = $this->getElementMapper()->fromArray($element);
        }

        $templateConfig['elements'] = $elements;
        $templateConfig['paperPage'] = $this->getService()->getDi()->newInstance(
            PaperPage::class,
            $templateConfig['paperPage']
        );

        $template = $this->getService()->getTemplateFactory()->getTemplateForOrderEntity($templateConfig);
        return $this->getService()->getResponseFromOrderCollection($orders, $template);
    }
}