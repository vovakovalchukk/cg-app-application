<?php
namespace ShipStation\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Channel\Type as ChannelType;
use CG\ShipStation\Carrier\Service as CarrierService;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use ShipStation\Module;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AccountController extends AbstractActionController
{
    const ROUTE = 'Account';
    const ROUTE_SAVE = 'Save';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var CarrierService */
    protected $carrierService;
    /** @var AccountService */
    protected $accountService;
    /** @var Cryptor */
    protected $cryptor;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        CarrierService $carrierService,
        AccountService $accountService,
        Cryptor $cryptor
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->carrierService = $carrierService;
        $this->accountService = $accountService;
        $this->cryptor = $cryptor;
    }

    public function setupAction(): ViewModel
    {
        $channelName = $this->params('channel');
        $carrier = $this->carrierService->getCarrierByChannelName($channelName);
        $accountId = $this->params()->fromQuery('accountId');
        if ($accountId) {
            $account = $this->accountService->fetch($accountId);
            $credentials = $this->cryptor->decrypt($account->getCredentials());
        }

        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('ship-station/setup')
            ->setVariable('isHeaderBarVisible', false)
            ->setVariable('subHeaderHide', true)
            ->setVariable('isSidebarVisible', false)
            ->setVariable('accountId', $accountId)
            ->setVariable('channelName', $channelName)
            ->addChild($this->getButtonView('linkAccount', 'Link Account'), 'linkAccount')
            ->addChild($this->getButtonView('goBack', 'Go Back'), 'goBack');

        $goBackUrl = $this->plugin('url')->fromRoute($this->getAccountRoute(), ['type' => ChannelType::SHIPPING]);
        if ($accountId) {
            $goBackUrl .= '/'.$accountId;
        }
        $view->setVariable('goBackUrl', $goBackUrl);

        $saveRoute = implode('/', [Module::ROUTE, static::ROUTE, static::ROUTE_SAVE]);
        $saveUrl = $this->url()->fromRoute($saveRoute, ['channel' => $channelName]);
        $view->setVariable('saveUrl', $saveUrl);

        // TODO: get the fields to display and add them to the view

        return $view;
    }

    protected function getAccountRoute()
    {
        return implode('/', [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS]);
    }

    protected function getButtonView($id, $text)
    {
        $buttonView = $this->viewModelFactory->newInstance([
            'buttons' => true,
            'value' => $text,
            'id' => $id
        ]);
        $buttonView->setTemplate('elements/buttons.mustache');
        return $buttonView;
    }

    public function saveAction()
    {
        //TODO
    }
}