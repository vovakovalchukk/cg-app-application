<?php
namespace Orders\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Orders\Order\StoredFilters\Service;
use Orders\Order\Service as OrderService;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\View\Model\JsonModel;

class StoredFiltersController extends AbstractActionController
{
    const ROUTE_SAVE = 'save';
    const ROUTE_REMOVE = 'remove';

    protected $service;
    protected $orderService;
    protected $jsonModelFactory;

    public function __construct(Service $service, OrderService $orderService, JsonModelFactory $jsonModelFactory)
    {
        $this->setService($service)->setOrderService($orderService)->setJsonModelFactory($jsonModelFactory);
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

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    /**
     * @param $variables
     * @param $options
     * @return JsonModel
     */
    protected function newJsonModel($variables = null, $options = null)
    {
        return $this->getJsonModelFactory()->newInstance($variables, $options);
    }

    public function saveFilterAction()
    {
        $jsonModel = $this->newJsonModel(['saved' => false]);
        $userPreference = $this->getOrderService()->getActiveUserPreference();
        $this->getService()->addStoredFilter(
            $userPreference,
            $this->params()->fromPost('name'),
            $this->params()->fromPost('filter')
        );
        $this->getOrderService()->getUserPreferenceService()->save($userPreference);
        return $jsonModel->setVariable('saved', true);
    }

    public function removeFilterAction()
    {
        $jsonModel = $this->newJsonModel(['removed' => false]);
        $userPreference = $this->getOrderService()->getActiveUserPreference();
        $this->getService()->removeStoredFilter(
            $userPreference,
            $this->params()->fromPost('name')
        );
        $this->getOrderService()->getUserPreferenceService()->save($userPreference);
        return $jsonModel->setVariable('removed', true);
    }
} 