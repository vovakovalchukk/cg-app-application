<?php
namespace Messages\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Messages\Thread\Service;
use Zend\Mvc\Controller\AbstractActionController;

class ThreadJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';

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