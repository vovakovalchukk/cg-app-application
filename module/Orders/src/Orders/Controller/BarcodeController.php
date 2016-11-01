<?php
namespace Orders\Controller;

use CG\Order\Shared\Barcode as BarcodeDecoder;
use CG\Order\Shared\Entity as Order;
use CG_UI\View\Prototyper\JsonModelFactory;
use Orders\Module;
use Zend\Config\Config;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class BarcodeController extends AbstractActionController
{
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var BarcodeDecoder */
    protected $barcodeDecoder;
    /** @var Config */
    protected $config;

    protected $actionMap = [
        BarcodeDecoder::ACTION_VIEW => 'viewOrder',
        BarcodeDecoder::ACTION_DISPATCH => 'dispatchOrder',
    ];

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        BarcodeDecoder $barcodeDecoder,
        Config $config
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->barcodeDecoder = $barcodeDecoder;
        $this->config = $config;
    }

    public function submitAction()
    {
        $postData = $this->params()->fromPost();
        $view = $this->jsonModelFactory->newInstance();

        $orderAndAction = $this->barcodeDecoder->decodeBarcodeToOrderAndAction($postData['barcode']);
        $method = $this->actionMap[$orderAndAction['action']];
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
// TODO
    }
}