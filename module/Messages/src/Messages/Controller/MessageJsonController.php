<?php
namespace Messages\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG_Usage\Service as UsageService;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use Messages\Message\Service;
use Zend\Mvc\Controller\AbstractActionController;

class MessageJsonController extends AbstractActionController
{
    const ROUTE_ADD = 'Add Message';
    const ROUTE_ADD_URL = '/addMessage';

    protected $jsonModelFactory;
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

    public function addAction()
    {
        $this->checkUsage();

        $view = $this->jsonModelFactory->newInstance();
        $threadId = $this->params()->fromPost('threadId');
        $body = $this->params()->fromPost('body');
        if (!$threadId || !$body) {
            throw new \InvalidArgumentException(__METHOD__ . ' requires an threadId and body in the POST data');
        }

        $messageData = $this->service->createMessageForThreadForActiveUser($threadId, $body);

        // Don't call this return variable 'message' as that's used when there's an error
        return $view->setVariable('messageEntity', $messageData);
    }

    protected function checkUsage()
    {
        if ($this->usageService->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }
    }

    protected function setJsonModelFactory($jsonModelFactory)
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