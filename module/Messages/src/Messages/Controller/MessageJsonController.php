<?php
namespace Messages\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Messages\Message\Service;
use Zend\Mvc\Controller\AbstractActionController;

class MessageJsonController extends AbstractActionController
{
    const ROUTE_ADD = 'Add Message';
    const ROUTE_ADD_URL = '/addMessage';

    protected $jsonModelFactory;
    protected $service;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        Service $service
    ) {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setService($service);
    }

    public function addAction()
    {
        $view = $this->jsonModelFactory->newInstance();
        $threadId = $this->params()->fromPost('threadId');
        $body = $this->params()->fromPost('body');
        if (!$threadId || !$body) {
            throw new \InvalidArgumentException(__METHOD__ . ' requires an threadId and body in the POST data');
        }
        $resolve = $this->params()->fromPost('resolve', false);

        $messageData = $this->service->createMessageForThreadForActiveUser($threadId, $body, $resolve);

        // Don't call this return variable 'message' as that's used when there's an error
        return $view->setVariable('messageEntity', $messageData);
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
}