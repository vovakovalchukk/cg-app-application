<?php
namespace ShipStation\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Channel\Type as ChannelType;
use CG\ShipStation\Account\CreationService as AccountCreationService;
use CG\ShipStation\Carrier\Service as CarrierService;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use ShipStation\Setup\Factory as SetupFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AccountController extends AbstractActionController
{
    const ROUTE = 'Account';
    const ROUTE_SAVE = 'Save';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var CarrierService */
    protected $carrierService;
    /** @var AccountService */
    protected $accountService;
    /** @var Cryptor */
    protected $cryptor;
    /** @var AccountCreationService */
    protected $accountCreationService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var SetupFactory */
    protected $setupFactory;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        CarrierService $carrierService,
        AccountService $accountService,
        Cryptor $cryptor,
        AccountCreationService $accountCreationService,
        ActiveUserInterface $activeUserContainer,
        SetupFactory $setupFactory
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->carrierService = $carrierService;
        $this->accountService = $accountService;
        $this->cryptor = $cryptor;
        $this->accountCreationService = $accountCreationService;
        $this->activeUserContainer = $activeUserContainer;
        $this->setupFactory = $setupFactory;
    }

    public function setupAction(): ViewModel
    {
        $channelName = $this->params('channel');
        $carrier = $this->carrierService->getCarrierByChannelName($channelName);
        $accountId = $this->params()->fromQuery('accountId');
        $organisationUnitId = $this->activeUserContainer->getActiveUser()->getOrganisationUnitId();
        $account = null;
        $credentials = null;
        if ($accountId) {
            $account = $this->accountService->fetch($accountId);
            $credentials = $this->cryptor->decrypt($account->getCredentials());
        }
        $setup = ($this->setupFactory)($channelName, $this->viewModelFactory, $this->url(), $this->redirect());
        return $setup($carrier, $organisationUnitId, $account, $credentials);
    }

    public function saveAction()
    {
        // ZF2 replaces spaces in param names with underscores, need to undo that
        $params = [];
        foreach ($this->params()->fromPost() as $key => $value) {
            $params[str_replace('_', ' ', $key)] = $value;
        }
        $view = $this->jsonModelFactory->newInstance();
        $accountEntity = $this->accountCreationService->connectAccount(
            $this->activeUserContainer->getActiveUser()->getOrganisationUnitId(),
            $params['account'],
            $params
        );
        $url = $this->plugin('url')->fromRoute($this->getAccountRoute(), ["account" => $accountEntity->getId(), "type" => ChannelType::SHIPPING]);
        $url .= '/' . $accountEntity->getId();
        $view->setVariable('redirectUrl', $url);
        return $view;
    }

    protected function getAccountRoute()
    {
        return implode('/', [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS]);
    }
}