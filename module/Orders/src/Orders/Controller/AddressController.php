<?php
namespace Orders\Controller;

use CG\Locale\CountryNameByCode;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Order\Service\UserChange\Service as UserChangeService;
use CG\Order\Shared\UserChange\Mapper as UserChangeMapper;
use CG\Order\Shared\UserChange\Entity as UserChangeEntity;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Order\Client\Storage\Api as OrderApi;
use CG\Stdlib\Exception\Runtime\NotFound;

class AddressController extends AbstractActionController
{
    protected $jsonModelFactory;
    protected $service;
    protected $mapper;
    protected $orderApi;

    public function __construct(JsonModelFactory $jsonModelFactory,
                                UserChangeService $service,
                                UserChangeMapper $mapper,
                                OrderApi $orderApi)
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setService($service)
            ->setMapper($mapper)
            ->setOrderApi($orderApi);
    }

    public function updateAction()
    {
        $order = $this->fetchOrder();
        $userChanges = $this->params()->fromPost();
        unset($userChanges['eTag']);
        $this->ensureCountryCodeSetIfCountryChanged($userChanges);

        $userChange = $this->fetchUserChange($order, $userChanges);
        $this->getService()->save($userChange);

        $view = $this->getJsonModelFactory()->newInstance();
        $view->setVariable('eTag', $userChange->getStoredETag());
        return $view;
    }

    protected function ensureCountryCodeSetIfCountryChanged(array &$userChanges)
    {
        if (isset($userChanges['billingAddressCountry']) &&
            $userChanges['billingAddressCountry'] != '' &&
            !isset($userChanges['billingAddressCountryCode'])
        ) {
            $userChanges['billingAddressCountryCode'] = CountryNameByCode::getCountryCodeFromName($userChanges['billingAddressCountry']);
        }
        if (isset($userChanges['shippingAddressCountry']) &&
            $userChanges['shippingAddressCountry'] != '' &&
            !isset($userChanges['shippingAddressCountryCode'])
        ) {
            $userChanges['shippingAddressCountryCode'] = CountryNameByCode::getCountryCodeFromName($userChanges['shippingAddressCountry']);
        }
    }

    protected function fetchUserChange(OrderEntity $order, array $userChanges)
    {
        try {
            $userChange = $this->getService()->fetch($this->params()->fromRoute('order'));
        } catch (NotFound $e) {
            $userChange = null;
        }
        return $this->getService()->fromUserChangeArray($order, $userChanges, $userChange);
    }

    protected function fetchOrder()
    {
        $order = $this->getOrderApi()->fetch($this->params()->fromRoute('order'));
        return $order;
    }

    public function setService(UserChangeService $service)
    {
        $this->service = $service;
        return $this;
    }

    public function getService()
    {
        return $this->service;
    }

    public function setMapper(UserChangeMapper $mapper)
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

    public function setOrderApi(OrderApi $orderApi)
    {
        $this->orderApi = $orderApi;
        return $this;
    }

    public function getOrderApi()
    {
        return $this->orderApi;
    }
}