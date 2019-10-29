<?php
namespace Messages\Controller;

use CG_Access\UsageExceeded\Service as AccessUsageExceededService;
use CG_UI\View\Prototyper\JsonModelFactory;
use Messages\Message\Service as MessageService;
use Zend\Mvc\Controller\AbstractActionController;

class MessageJsonController extends AbstractActionController
{
    const ROUTE_ADD = 'Add Message';
    const ROUTE_ADD_URL = '/addMessage';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var MessageService */
    protected $messageService;
    /** @var AccessUsageExceededService */
    protected $accessUsageExceededService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        MessageService $messageService,
        AccessUsageExceededService $accessUsageExceededService
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->messageService = $messageService;
        $this->accessUsageExceededService = $accessUsageExceededService;
    }

    public function addAction()
    {
        $this->accessUsageExceededService->checkUsage();
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
}