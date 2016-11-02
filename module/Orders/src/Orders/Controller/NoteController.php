<?php
namespace Orders\Controller;

use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Order\Service\Note\Service as NoteService;
use CG\Order\Shared\Note\Mapper as NoteMapper;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use Orders\Order\Service as OrderService;

class NoteController extends AbstractActionController implements StatsAwareInterface
{
    use StatsTrait;

    const STAT_ORDER_ACTION_NOTED = 'orderAction.noted.%s.%d.%d';

    protected $jsonModelFactory;
    protected $service;
    protected $activeUserContainer;
    protected $mapper;
    protected $orderService;

    public function __construct(JsonModelFactory $jsonModelFactory,
                                NoteService $service,
                                ActiveUserInterface $activeUserContainer,
                                NoteMapper $mapper,
                                OrderService $orderService)
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setService($service)
            ->setActiveUserContainer($activeUserContainer)
            ->setMapper($mapper)
            ->setOrderService($orderService);

        $this->view = $this->getJsonModelFactory()->newInstance();
    }

    public function indexAction()
    {
        try {
            $noteCollection = $this->getService()->fetchCollectionByOrderIds([$this->params('order')]);
            $notes = $this->getOrderService()->getNamesFromOrderNotes($noteCollection);
        } catch (NotFound $e) {
            $notes = [];
        }
        $this->view->setVariables(["notes" => $notes]); 
        return $this->view;
    }

    public function createAction()
    {
        $order = $this->getOrderService()->getOrder($this->params('order'));
        $note = $this->getMapper()->fromArray(
            array(
                'orderId' => $this->params('order'),
                'userId' => $this->getActiveUserContainer()->getActiveUser()->getId(),
                'timestamp' => date(DateTime::FORMAT, time()),
                'note' => $this->params()->fromPost('note'),
                'organisationUnitId' => $order->getOrganisationUnitId()
            )
        );
        $this->getService()->save($note);
        $this->view->setVariables(["note" => $note->toArray()]);
        $this->statsIncrement(
            static::STAT_ORDER_ACTION_NOTED, [
                $order->getChannel(),
                $this->getActiveUserContainer()->getActiveUserRootOrganisationUnitId(),
                $this->getActiveUserContainer()->getActiveUser()->getId()
            ]
        );
        return $this->view;
    }

    public function deleteAction()
    {
        $noteId = $this->params()->fromPost('noteId');
        $this->getService()->removeById($noteId, $this->params('order'));
        return $this->view;
    }

    protected function updateAction()
    {
        $note = $this->getService()->fetch($this->params()->fromPost('noteId'), $this->params('order'));
        $note->setNote($this->params()->fromPost('note'))
            ->setUserId($this->getActiveUserContainer()->getActiveUser()->getId())
            ->setTimestamp(date(DateTime::FORMAT, time()));
        $this->getService()->save($note);
        return $this->view;
    }

    public function setService(NoteService $service)
    {
        $this->service = $service;
        return $this;
    }

    public function getService()
    {
        return $this->service;
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

    public function setMapper(NoteMapper $mapper)
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
}