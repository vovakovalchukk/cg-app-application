<?php
namespace Messages\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Messages\Thread\Service;
use Zend\Mvc\Controller\AbstractActionController;

class ThreadJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';
    const ROUTE_AJAX_URL = '/ajax';
    const ROUTE_THREAD = 'Thread';
    const ROUTE_THREAD_URL = '/thread';

    protected $jsonModelFactory;
    protected $service;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        Service $service
    ) {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setService($service);
    }

    public function ajaxAction()
    {
        $view = $this->jsonModelFactory->newInstance();
        $filters = $this->params()->fromPost('filter', []);

        $threadsData = $this->service->fetchThreadDataForFilters($filters);

        return $view->setVariable('threads', $threadsData);
    }

    public function threadAction()
    {
        $view = $this->jsonModelFactory->newInstance();
        $id = $this->params()->fromPost('id');
        if (!$id) {
            throw new \InvalidArgumentException(__METHOD__ . ' requires an id in the POST data');
        }

        $threadData = $this->service->fetchThreadDataForId($id);

        return $view->setVariable('thread', $threadData);
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