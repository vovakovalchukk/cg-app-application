<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Service\Note\Service;

class BarcodeController extends AbstractActionController
{
    protected $jsonModelFactory;
    protected $service;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        Service $service)
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setService($service);
    }

    public function submitAction()
    {
        $postData = $this->params()->fromPost();

        //$result = $this->service->submitBarcode($postData['barcode']);

        $view = $this->jsonModelFactory->newInstance();
        $view->setVariable('result', 0);
        return $view;
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }
}