<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Order\Service\Tracking\Service as TrackingService;
use CG\Order\Shared\Tracking\Mapper as TrackingMapper;
use CG\Order\Shared\Tracking\Entity as TrackingEntity;
use CG\Order\Client\Service as OrderService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Http\Exception\Exception3xx\NotModified;

class TrackingController extends AbstractActionController
{
    protected $jsonModelFactory;
    protected $trackingService;
    protected $mustacheRenderer;
    protected $mapper;
    protected $orderService;
    protected $activeUserContainer;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        TrackingService $service,
        TrackingMapper $mapper,
        OrderService $orderService)
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setTrackingService($service)
            ->setMapper($mapper)
            ->setOrderService($orderService)
            ->setActiveUserContainer($activeUserContainer);
    }


    // try save, if not then check error, if they are same then save back o.g etag
    public function updateAction()
    {     
        $tracking = $this->fetchTracking();
        $this->update($tracking);
        try {
            $this->getTrackingService()->save($tracking);
            $this->getTrackingService()->createGearmanJob($this->fetchOrder());
        } catch (NotModified $ex) {
            // If not modified then noop
        }

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
        $this->getTrackingService()->createGearmanJob($this->fetchOrder()); 
        $view = $this->getJsonModelFactory()->newInstance();
        $view->setVariable('eTag', '');
        return $view;
    }

    protected function create()
    {
        $tracking = $this->getMapper()->fromArray(
            [
                'userId' =>  $this->getActiveUserContainer()->getActiveUser()->getId(),
                'orderId' => $this->params('order'),
                'number' => $this->params()->fromPost('trackingNumber'),
                'carrier' => $this->params()->fromPost('carrier'),
                'timestamp' => date(StdlibDateTime::FORMAT),
                'organisationUnitId' => $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId()
            ]
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
        return $this->getOrderService()->fetch($this->params()->fromRoute('order'));
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
            $tracking = $this->create();
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
}