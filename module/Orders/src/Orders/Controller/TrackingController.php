<?php
namespace Orders\Controller;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Tracking\Service;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Tracking\Entity as Tracking;
use CG\Order\Shared\Tracking\Filter;
use CG\Order\Shared\Tracking\Mapper;
use CG\Order\Shared\Tracking\Status;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class TrackingController extends AbstractActionController implements StatsAwareInterface
{
    use StatsTrait;

    const STAT_ORDER_ACTION_TRACKED = 'orderAction.tracked.%s.%d.%d';

    protected $jsonModelFactory;
    protected $trackingService;
    protected $mustacheRenderer;
    protected $mapper;
    protected $orderService;
    protected $activeUserContainer;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        Service $service,
        Mapper $mapper,
        OrderService $orderService
    ) {
        $this
            ->setJsonModelFactory($jsonModelFactory)
            ->setService($service)
            ->setMapper($mapper)
            ->setOrderService($orderService)
            ->setActiveUserContainer($activeUserContainer);
    }

    public function validateAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        if (!$this->params()->fromPost('trackingNumber')) {
            $view->setVariable('valid', true);
            return $view;
        }
        $filter = (new Filter(1))
            ->setOrganisationUnitId($this->getActiveUserContainer()->getActiveUser()->getOuList())
            ->setNumber([$this->params()->fromPost('trackingNumber')]);

        try {
            $this->getService()->fetchCollectionByFilter($filter);
            $view->setVariable('valid', false);
        } catch (NotFound $exception) {
            $view->setVariable('valid', true);
        }
        return $view;
    }

    public function updateAction()
    {
        $tracking = $this->fetchTracking();
        if (!$tracking) {
            $tracking = $this->create();
        }
        $tracking = $this->update($tracking);
        try {
            $this->getService()->save($tracking);
            $order = $this->fetchOrder();
            $this->getService()->createGearmanJob($order);
            $this->statsIncrement(
                static::STAT_ORDER_ACTION_TRACKED, [
                    $order->getChannel(),
                    $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId(),
                    $this->getActiveUserContainer()->getActiveUser()->getId()
                ]
            );
        } catch (NotModified $ex) {
            // If not modified then noop
        }
        $view = $this->getJsonModelFactory()->newInstance();
        $view->setVariable('eTag', $tracking->getStoredETag());
        return $view;
    }

    public function deleteAction()
    {
        $tracking = $this->fetchTracking();
        if ($tracking) {
            $this->getService()->remove($tracking);
        }
        $this->getService()->createGearmanJob($this->fetchOrder()); 
        $view = $this->getJsonModelFactory()->newInstance();
        $view->setVariable('eTag', '');
        return $view;
    }

    /**
     * @return Tracking
     */
    protected function create()
    {
        $tracking = $this->getMapper()->fromArray(
            [
                'userId' =>  $this->getActiveUserContainer()->getActiveUser()->getId(),
                'orderId' => $this->params('order'),
                'number' => $this->params()->fromPost('trackingNumber'),
                'carrier' => $this->params()->fromPost('carrier'),
                'timestamp' => date(StdlibDateTime::FORMAT),
                'organisationUnitId' => $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId(),
                'status' => Status::PENDING,
            ]
        );
        return $tracking;
    }

    protected function update(Tracking $tracking)
    {
        $tracking->setNumber($this->params()->fromPost('trackingNumber'))
            ->setCarrier($this->params()->fromPost('carrier'))
            ->setStatus(Status::PENDING);
        return $tracking;
    }

    /**
     * @return Order
     */
    protected function fetchOrder()
    {
        return $this->getOrderService()->fetch($this->params()->fromRoute('order'));
    }

    protected function fetchTracking(): ?Tracking
    {
        try {
            $orderId = $this->params('order');
            $trackingId = $this->params()->fromPost('trackingId');

            /* @var $trackingCollection \CG\Order\Shared\Tracking\Collection */
            $trackingCollection = $this->getService()->fetchCollectionByOrderIds([$orderId]);
            $trackings = $trackingCollection->getByOrderId($orderId);

            $tracking = $trackings->getById($trackingId);
            if ($tracking instanceof Tracking) {
                return $tracking;
            }

            $trackings->rewind();
            $tracking = $trackings->current();
        } catch (NotFound $e) {
            $tracking = null;
        }

        return $tracking;
    }

    /**
     * @return self
     */
    protected function setService(Service $service)
    {
        $this->trackingService = $service;
        return $this;
    }

    /**
     * @return Service
     */
    protected function getService()
    {
        return $this->trackingService;
    }

    /**
     * @return self
     */
    protected function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return Mapper
     */
    protected function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return self
     */
    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    /**
     * @return self
     */
    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @return self
     */
    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return ActiveUserInterface
     */
    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }
}
