<?php
namespace Orders\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Order\Service\Alert\Service as AlertService;
use CG\Order\Shared\Alert\Mapper as AlertMapper;
use CG\Order\Shared\Alert\Entity as AlertEntity;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class AlertController extends AbstractActionController
{
    protected $jsonModelFactory;
    protected $service;
    protected $activeUserContainer;
    protected $mapper;

    public function __construct(JsonModelFactory $jsonModelFactory,
                                AlertService $service,
                                ActiveUserInterface $activeUserContainer,
                                AlertMapper $mapper)
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setService($service)
            ->setActiveUserContainer($activeUserContainer)
            ->setMapper($mapper);
    }

    public function setAction()
    {
        $alert = $this->fetchAlert();
        $alert = is_null($alert) ? $this->create() : $this->update($alert);
        $view = $this->getJsonModelFactory()->newInstance();
        $view->setVariable("eTag", $alert->getETag());
        return $view;
    }

    public function deleteAction()
    {
        $alert = $this->fetchAlert();
        if ($alert) {
            $this->getService()->remove($alert);
        }
        $view = $this->getJsonModelFactory()->newInstance();
        $view->setVariable("eTag", "");
        return $view;
    }

    protected function create()
    {
        $alert = $this->getMapper()->fromArray(
            array(
                "userId" => $this->getActiveUserContainer()->getActiveUser()->getId(),
                "alert" => $this->params()->fromPost('alert'),
                "timestamp" => date("Y-m-d H:i:s", time()),
                "orderId" => $this->params('order')
            )
        );
        $this->getService()->save($alert);
        return $alert;
    }

    protected function update(AlertEntity $alert)
    {
        $alert->setAlert($this->params()->fromPost('alert'))
            ->setUserId($this->getActiveUserContainer()->getActiveUser()->getId())
            ->setTimestamp(date("Y-m-d H:i:s", time()));
        $alert->setStoredETag($this->params()->fromPost('eTag'));
        $this->getService()->save($alert);
        return $alert;
    }

    protected function fetchAlert()
    {
        try {
            $orderId = $this->params('order');
            $alertCollection = $this->getService()->fetchCollectionByOrderIds([$orderId]);
            $alerts = $alertCollection->getByOrderId($orderId);
            $alerts->rewind();
            $alert = $alerts->current();
        } catch (NotFound $e) {
            $alert = null;
        }
        return $alert;
    }

    public function setService(AlertService $service)
    {
        $this->service = $service;
        return $this;
    }

    public function getService()
    {
        return $this->service;
    }

    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    public function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }

    public function setMapper(AlertMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }
}