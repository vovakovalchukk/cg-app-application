<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Orders\Order\StoredFilters\Service;
use Orders\Order\Service as OrderService;

class StoredFiltersController extends AbstractActionController
{
    protected $service;
    protected $orderService;

    public function __construct(Service $service, OrderService $orderService)
    {
        $this->setService($service)->setOrderService($orderService);
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

    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    public function saveFilterAction()
    {
        $userPreference = $this->getOrderService()->getActiveUserPreference();
        $this->getService()->addStoredFilter(
            $userPreference,
            $this->params()->fromPost('name'),
            $this->params()->fromPost('filter')
        );
        $this->getOrderService()->getUserPreferenceService()->save($userPreference);
    }

    public function removeFilterAction()
    {
        $userPreference = $this->getOrderService()->getActiveUserPreference();
        $this->getService()->removeStoredFilter(
            $userPreference,
            $this->params()->fromPost('name')
        );
        $this->getOrderService()->getUserPreferenceService()->save($userPreference);
    }
} 