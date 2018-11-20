<?php
namespace Orders\Controller;

use CG\Order\Shared\Barcode as BarcodeDecoder;
use CG\Order\Shared\Entity as Order;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use Orders\Module;
use CG\Order\Client\Action\Service;
use Zend\Config\Config;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class BarcodeController extends AbstractActionController
{
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var BarcodeDecoder */
    protected $barcodeDecoder;
    /** @var Service */
    protected $service;
    /** @var Config */
    protected $config;
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;

    protected $actionMap = [
        BarcodeDecoder::ACTION_VIEW => 'viewOrder',
        BarcodeDecoder::ACTION_DISPATCH => 'dispatchOrder',
    ];

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        BarcodeDecoder $barcodeDecoder,
        Service $service,
        Config $config,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->barcodeDecoder = $barcodeDecoder;
        $this->service = $service;
        $this->config = $config;
        $this->activeUserContainer = $activeUserContainer;
    }

    public function submitAction()
    {
        $postData = $this->params()->fromPost();
        $view = $this->jsonModelFactory->newInstance();

        $orderAndAction = $this->barcodeDecoder->decodeBarcodeToOrderAndAction($postData['barcode']);
        $method = $this->actionMap[$orderAndAction['action']];

        if (!method_exists($this, $method)) {
            $msg = sprintf(BarcodeDecoder::EXC_INVALID_BARCODE, __METHOD__, $postData['barcode']);
            throw new \InvalidArgumentException($msg);
        }

        $this->$method($orderAndAction['order'], $view);

        // This action can be called from App or Admin
        $adminHost = 'https://' . $this->config->get('cg')->get('sites')->get('admin')->get('host');
        $this->getResponse()->getHeaders()->addHeaders([
            'Access-Control-Allow-Origin'       => $adminHost,
            'Access-Control-Allow-Credentials'  => 'true',
        ]);

        return $view;
    }

    protected function viewOrder(Order $order, JsonModel $view)
    {
        $url = $this->url()->fromRoute(Module::ROUTE . '/order', ['order' => $order->getId()], ['force_canonical' => true]);
        $view->setVariable('url', $url);
    }

    protected function dispatchOrder(Order $order, JsonModel $view)
    {
        $this->service->dispatchOrder(
            $order,
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            $this->activeUserContainer->getActiveUser()->getId()
        );;
        $view->setVariable('message', $this->translate('Order ' . $order->getExternalId() . ' is now being dispatched'));
    }
}