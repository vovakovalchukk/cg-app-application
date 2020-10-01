<?php
namespace Messages\Controller;

use CG_Access\UsageExceeded\Service as AccessUsageExceededService;
use CG_UI\View\Prototyper\JsonModelFactory;
use Messages\Thread\OrdersInformation\LinkTextBuilder;
use Messages\Thread\Service as ThreadService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ThreadJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';
    const ROUTE_AJAX_URL = '/ajax';
    const ROUTE_THREAD = 'Thread';
    const ROUTE_THREAD_URL = '/thread';
    const ROUTE_SAVE = 'Save';
    const ROUTE_SAVE_URL = '/save';
    const ROUTE_COUNTS = 'Counts';
    const ROUTE_COUNTS_URL = '/counts';

    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;
    /** @var ThreadService $threadService */
    protected $threadService;
    /** @var AccessUsageExceededService */
    protected $accessUsageExceededService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ThreadService $threadService,
        AccessUsageExceededService $accessUsageExceededService
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->threadService = $threadService;
        $this->accessUsageExceededService = $accessUsageExceededService;
    }

    public function ajaxAction()
    {
        /** @var JsonModel $view */
        $view = $this->jsonModelFactory->newInstance();
        $filters = $this->params()->fromPost('filter', []);
        if ($threadId = $this->params('threadId')) {
            $filters['id'] = $threadId;
        }
        $page = $this->params()->fromPost('page');
        $sortDescending = filter_var($this->params()->fromPost('sortDescending', true), FILTER_VALIDATE_BOOLEAN);

        $threadsData = $this->threadService->fetchThreadDataForFilters($filters, $page, $sortDescending);

        return $view->setVariable('threads', $threadsData);
    }

    public function threadAction()
    {
        /** @var JsonModel $view */
        $view = $this->jsonModelFactory->newInstance();
        $id = $this->params()->fromPost('id');
        if (!$id) {
            throw new \InvalidArgumentException(__METHOD__ . ' requires an id in the POST data');
        }

        $threadData = $this->threadService->fetchThreadDataForId($id);

        return $view->setVariable('thread', $threadData);
    }

    public function saveAction()
    {
        $this->accessUsageExceededService->checkUsage();
        /** @var JsonModel $view */
        $view = $this->jsonModelFactory->newInstance();
        $id = $this->params()->fromPost('id');
        if (!$id) {
            throw new \InvalidArgumentException(__METHOD__ . ' requires an id in the POST data');
        }
        $assignedUserId = $this->params()->fromPost('assignedUserId', false);
        $status = $this->params()->fromPost('status', null);

        $threadData = $this->threadService->updateThreadAndReturnData($id, $assignedUserId, $status);

        return $view->setVariable('thread', $threadData);
    }

    public function countsAction()
    {
        $counts = [
            'orders' => 0,
        ];
        $linkText = LinkTextBuilder::LINK_TEXT_PLACEHOLDER;

        $threadId = $this->params('threadId');
        if ($threadId) {
            $ordersInformation = $this->threadService->getOrdersInformationForId($threadId);
            $counts['orders'] = $ordersInformation->getCount();
            $linkText = $ordersInformation->getLinkText();
        }

        return $this->jsonModelFactory->newInstance(['counts' => $counts, 'linkText' => $linkText]);
    }
}
