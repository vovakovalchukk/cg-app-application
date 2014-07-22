<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Order\Service\Tracking\Service as TrackingService;
use CG\Order\Shared\Tracking\Mapper as TrackingMapper;
use CG\Order\Shared\Tracking\Entity as TrackingEntity;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Order\Client\Service as OrderService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG\Channel\Gearman\Generator\Order\Dispatch as GearmanOrderGenerator;

class TrackingController extends AbstractActionController
{
    protected $jsonModelFactory;
    protected $trackingService;
    protected $mustacheRenderer;
    protected $mapper;
    protected $orderService;
    protected $gearmanOrderGenerator;

    public function __construct(ActiveUserInterface $activeUserContainer,
                                JsonModelFactory $jsonModelFactory,
                                TrackingService $service,
                                TrackingMapper $mapper,
                                OrderService $orderService,
                                GearmanOrderGenerator $gearmanOrderGenerator)
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setTrackingService($service)
            ->setMapper($mapper)
            ->setOrderService($orderService)
            ->setActiveUserContainer($activeUserContainer)
            ->setGearmanOrderGenerator($gearmanOrderGenerator);
    }

    public function updateAction()
    {
        $tracking = $this->fetchTracking();
        $tracking = is_null($tracking) ? $this->create() : $this->update($tracking);
        $this->getTrackingService()->save($tracking);
        $this->createGearmanJob();
        $view = $this->getJsonModelFactory()->newInstance();
        $view->setVariable('eTag', $tracking->getETag());
        return $view;
    }

    public function deleteAction()
    {
        $tracking = $this->fetchTracking();
        if ($tracking) {
            $this->getTrackingService()->remove($tracking);
        }
        $this->createGearmanJob();
        $view = $this->getJsonModelFactory()->newInstance();
        $view->setVariable('eTag', '');
        return $view;
    }

    protected function createGearmanJob()
    {
        $order = $this->getOrderService()->fetch($this->params('order'));
        if(!$order->getDispatchDate()) {
            return;
        }      
        $this->getGearmanOrderGenerator()->generateJob($order->getAccountId(), $order);
        return $this;
    }

    protected function create()
    {
        $order = $this->getOrderService()->fetch($this->params('order'));
        $tracking = $this->getMapper()->fromArray(
            array(
                'userId' =>  $this->getActiveUserContainer()->getActiveUser()->getId(),
                'orderId' => $this->params('order'),
                'number' => $this->params()->fromPost('trackingNumber'),
                'carrier' => $this->params()->fromPost('carrier'),
                'timestamp' => $order->getDispatchDate(),
                'id' => NULL,
                'organisationUnitId' => $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId()
            )
        );
        return $tracking;
    }

    protected function update(TrackingEntity $tracking)
    {
        $tracking->setNumber($this->params()->fromPost('trackingNumber'))
            ->setCarrier($this->params()->fromPost('carrier'));
        $tracking->setStoredETag($this->params()->fromPost('eTag'));
        return $tracking;
    }

    protected function fetchOrder()
    {
        $order = $this->getOrderService()->fetch($this->params()->fromRoute('order'));
        return $order;
    }

    protected function fetchTracking()
    {
        try {
            $orderId = $this->params('order');
            $trackingCollection = $this->getTrackingService()->fetchCollectionByOrderIds([$orderId]);
            $trackings = $trackingCollection->getByOrderId($orderId);
            $trackings->rewind();
            $tracking = $trackings->current();
        } catch (NotFound $e) {
            $tracking = null;
        }

        return $tracking;
    }

    public function setTrackingService(TrackingService $service)
    {
        $this->trackingService = $service;
        return $this;
    }

    public function getTrackingService()
    {
        return $this->trackingService;
    }

    public function setMapper(TrackingMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    public function getOrderService()
    {
        return $this->orderService;
    }

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    protected function getGearmanOrderGenerator() {
        return $this->gearmanOrderGenerator;
    }

    protected function setGearmanOrderGenerator(GearmanOrderGenerator $gearmanOrderGenerator) {
        $this->gearmanOrderGenerator = $gearmanOrderGenerator;
        return $this;
    }
}