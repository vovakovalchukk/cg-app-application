<?php
namespace Messages\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG_Usage\Service as UsageService;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use Messages\Thread\Service;
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
    /** @var Service $service */
    protected $service;
    /** @var UsageService */
    protected $usageService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        Service $service,
        UsageService $usageService
    ) {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setService($service)
            ->setUsageService($usageService);
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

        $threadsData = $this->service->fetchThreadDataForFilters($filters, $page);

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

        $threadData = $this->service->fetchThreadDataForId($id);

        return $view->setVariable('thread', $threadData);
    }

    public function saveAction()
    {
        $this->checkUsage();

        /** @var JsonModel $view */
        $view = $this->jsonModelFactory->newInstance();
        $id = $this->params()->fromPost('id');
        if (!$id) {
            throw new \InvalidArgumentException(__METHOD__ . ' requires an id in the POST data');
        }
        $assignedUserId = $this->params()->fromPost('assignedUserId', false);
        $status = $this->params()->fromPost('status', null);

        $threadData = $this->service->updateThreadAndReturnData($id, $assignedUserId, $status);

        return $view->setVariable('thread', $threadData);
    }

    public function countsAction()
    {
        $counts = [
            'orders' => 0,
        ];

        $threadId = $this->params('threadId');
        if ($threadId) {
            $counts['orders'] = $this->service->getOrderCountForId($threadId);
        }

        return $this->jsonModelFactory->newInstance(['counts' => $counts]);
    }

    protected function checkUsage()
    {
        if ($this->usageService->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    protected function setUsageService(UsageService $usageService)
    {
        $this->usageService = $usageService;
        return $this;
    }
}
