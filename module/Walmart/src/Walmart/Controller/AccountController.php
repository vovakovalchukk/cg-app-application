<?php
namespace Walmart\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Channel\Type as ChannelType;
use CG\Walmart\Account\CreationService as AccountCreationService;
use CG\Walmart\Credentials;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Walmart\Module;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AccountController extends AbstractActionController
{
    const ROUTE_SETUP = 'Setup';
    const ROUTE_SAVE = 'Save';

    /** @var AccountService */
    protected $accountService;
    /** @var Cryptor */
    protected $cryptor;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var AccountCreationService */
    protected $accountCreationService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(
        AccountService $accountService,
        Cryptor $cryptor,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        AccountCreationService $accountCreationService,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->accountService = $accountService;
        $this->cryptor = $cryptor;
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->accountCreationService = $accountCreationService;
        $this->activeUserContainer = $activeUserContainer;
    }

    public function indexAction()
    {
        $accountId = $this->params()->fromQuery('accountId');
        $credentials = null;
        $externalData = [];

        $view = $this->getBasicIndexView($accountId);
        $view
            ->addChild($this->getButtonView('linkAccount', 'Link Account'), 'linkAccount')
            ->addChild($this->getButtonView('goBack', 'Go Back'), 'goBack');

        $goBackUrl = $this->url()->fromRoute($this->getAccountRoute(), ['type' => ChannelType::SALES]);
        if ($accountId) {
            $goBackUrl .= '/'.$accountId;
            $account = $this->accountService->fetch($accountId);
            $credentials = $this->cryptor->decrypt($account->getCredentials());
            $externalData = $account->getExternalData();
        }
        $view->setVariable('goBackUrl', $goBackUrl);

        $saveRoute = implode('/', [Module::ROUTE, static::ROUTE_SETUP, static::ROUTE_SAVE]);
        $saveUrl = $this->url()->fromRoute($saveRoute);
        $view->setVariable('saveUrl', $saveUrl);

        $this->addFieldsToView($view, $credentials, $externalData);
        
        return $view;
    }

    protected function getBasicIndexView(int $accountId = null): ViewModel
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('walmart/setup');
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        $view->setVariable('isSidebarVisible', false);
        $view->setVariable('accountId', $accountId);
        return $view;
    }

    protected function getAccountRoute()
    {
        return implode('/', [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS]);
    }

    protected function addFieldsToView(ViewModel $view, Credentials $credentials = null, $externalData = []): ViewModel
    {
        $clientIdField = $this->getTextView('clientId', 'Client ID', $credentials ? $credentials->getClientId() : '');
        $clientSecretField = $this->getPasswordView('clientSecret', 'Client Secret', $credentials ? $credentials->getClientSecret() : '');
        $fulfillmentLagTime = $this->getTextView('fulfillmentLagTime', 'Fulfillment Lag Time', isset($externalData['fulfillmentLagTime']) ? $externalData['fulfillmentLagTime'] : '1');
        $view
            ->addChild($clientIdField, 'clientId')
            ->addChild($clientSecretField, 'clientSecret')
            ->addChild($fulfillmentLagTime, 'fulfillmentLagTime');
        return $view;
    }

    protected function getButtonView(string $id, string $text): ViewModel
    {
        $buttonView = $this->viewModelFactory->newInstance([
            'buttons' => true,
            'value' => $text,
            'id' => $id
        ]);
        $buttonView->setTemplate('elements/buttons.mustache');
        return $buttonView;
    }

    protected function getTextView(string $id, string $label, ?string $value = '', bool $required = true): ViewModel
    {
        $textView = $this->viewModelFactory->newInstance([
            'name' => $id,
            'id' => $id,
            'label' => $label,
            'value' => $value,
            'class' => ($required ? 'required' : ''),
        ]);
        $textView->setTemplate('elements/text.mustache');
        return $textView;
    }

    protected function getPasswordView(string $id, string $label, ?string $value = '', bool $required = false): ViewModel
    {
        $passwordView = $this->getTextView($id, $label, $value, $required);
        $passwordView->setVariable('type', 'password');
        return $passwordView;
    }

    public function saveAction()
    {
        $params = $this->params()->fromPost();
        $view = $this->jsonModelFactory->newInstance();
        $accountEntity = $this->accountCreationService->connectAccount(
            $this->activeUserContainer->getActiveUser()->getOrganisationUnitId(),
            $params['account'],
            $params
        );
        $url = $this->url()->fromRoute($this->getAccountRoute(), ["account" => $accountEntity->getId(), "type" => ChannelType::SALES]);
        $url .= '/' . $accountEntity->getId();
        $view->setVariable('redirectUrl', $url);
        return $view;
    }
}