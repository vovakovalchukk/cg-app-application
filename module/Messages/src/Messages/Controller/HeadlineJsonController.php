<?php
namespace Messages\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Messages\Headline\Service;
use Zend\Mvc\Controller\AbstractActionController;

class HeadlineJsonController extends AbstractActionController
{
    const ROUTE_HEADLINE = 'Headline';
    const ROUTE_HEADLINE_URL = '/headline';

    protected $jsonModelFactory;
    protected $service;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        Service $service
    ) {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setService($service);
    }

    public function headlineAction()
    {
        $view = $this->jsonModelFactory->newInstance();

        $headlineData = $this->service->fetchHeadlineDataForActiveUser();

        return $view->setVariable('headline', $headlineData);
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