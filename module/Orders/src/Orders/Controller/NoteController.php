<?php
namespace Orders\Controller;

use CG\Order\Service\Note\Service as NoteService;
use CG\Order\Shared\Note\Mapper as NoteMapper;
use CG\Stats\StatsAwareInterface;
use CG\Stats\StatsTrait;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use Orders\Controller\Helpers\OrderNotes as OrderNotesHelper;
use Orders\Order\Service as OrderService;
use Zend\Mvc\Controller\AbstractActionController;

class NoteController extends AbstractActionController implements StatsAwareInterface
{
    use StatsTrait;

    const STAT_ORDER_ACTION_NOTED = 'orderAction.noted.%s.%d.%d';

    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;
    /** @var NoteService $service */
    protected $service;
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var NoteMapper $mapper */
    protected $mapper;
    /** @var OrderService $orderService */
    protected $orderService;
    /** @var OrderNotesHelper $orderNoteHelper */
    protected $orderNoteHelper;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        NoteService $service,
        ActiveUserInterface $activeUserContainer,
        NoteMapper $mapper,
        OrderService $orderService,
        OrderNotesHelper $orderNoteHelper
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->service = $service;
        $this->activeUserContainer = $activeUserContainer;
        $this->mapper = $mapper;
        $this->orderService = $orderService;
        $this->orderNoteHelper = $orderNoteHelper;
    }

    public function indexAction()
    {
        $view = $this->jsonModelFactory->newInstance();
        try {
            $noteCollection = $this->service->fetchCollectionByOrderIds([$this->params('order')]);
            $notes = $this->orderNoteHelper->getNamesFromOrderNotes($noteCollection);
        } catch (NotFound $e) {
            $notes = [];
        }
        $view->setVariables(['notes' => $notes]);
        return $view;
    }

    public function createAction()
    {
        $view = $this->jsonModelFactory->newInstance();
        $order = $this->orderService->getOrder($this->params('order'));
        $note = $this->mapper->fromArray(
            array(
                'orderId' => $this->params('order'),
                'userId' => $this->activeUserContainer->getActiveUser()->getId(),
                'timestamp' => date(DateTime::FORMAT, time()),
                'note' => $this->params()->fromPost('note'),
                'organisationUnitId' => $order->getOrganisationUnitId()
            )
        );
        $this->service->save($note);
        $note = $note->toArray();
        $note['timestamp'] = date(DateTime::FORMAT_UI, strtotime($note['timestamp']));
        $view->setVariables(["note" => $note]);
        $this->statsIncrement(
            static::STAT_ORDER_ACTION_NOTED, [
                $order->getChannel(),
                $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                $this->activeUserContainer->getActiveUser()->getId()
            ]
        );
        return $view;
    }

    public function deleteAction()
    {
        $view = $this->jsonModelFactory->newInstance();
        $noteId = $this->params()->fromPost('noteId');
        $this->service->removeById($noteId, $this->params('order'));
        return $view;
    }

    protected function updateAction()
    {
        $view = $this->jsonModelFactory->newInstance();
        $note = $this->service->fetch($this->params()->fromPost('noteId'), $this->params('order'));
        $note->setNote($this->params()->fromPost('note'))
            ->setUserId($this->activeUserContainer->getActiveUser()->getId())
            ->setTimestamp(date(DateTime::FORMAT, time()));
        $this->service->save($note);
        return $view;
    }
}
