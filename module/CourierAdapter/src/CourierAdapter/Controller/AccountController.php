<?php
namespace CourierAdapter\Controller;

use CG\Channel\Type as ChannelType;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Account\LocalAuthInterface;
use CG\CourierAdapter\Exception\InvalidCredentialsException;
use CG\CourierAdapter\Provider\Account as CAAccountSetup;
use CG\CourierAdapter\Provider\Account\CreationService as AccountCreationService;
use CG\CourierAdapter\Provider\Implementation\PrepareAdapterImplementationFieldsTrait;
use CG\Stdlib\Exception\Runtime\ValidationException;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CourierAdapter\Account\Service as CAModuleAccountService;
use CourierAdapter\Module;
use InvalidArgumentException;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Form\Element as ZendFormElement;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Zend\Stdlib\Http\FileResponse;

class AccountController extends AbstractActionController
{
    use PrepareAdapterImplementationFieldsTrait;

    const ROUTE = 'Account';
    const ROUTE_SAVE = 'Save';
    const ROUTE_REQUEST_SEND = 'Send';
    const ROUTE_SAVE_CONFIG = 'Save Config';
    const ROUTE_TEST_PACK_FILE = 'Test Pack File';

    /** @var AccountCreationService */
    protected $accountCreationService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var CAModuleAccountService */
    protected $caModuleAccountService;

    public function __construct(
        AccountCreationService $accountCreationService,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUserContainer,
        CAModuleAccountService $caModuleAccountService
    ) {
        $this->setAccountCreationService($accountCreationService)
            ->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setActiveUserContainer($activeUserContainer)
            ->setCaModuleAccountService($caModuleAccountService);
    }

    public function setupAction()
    {
        $channelName = $this->params('channel');
        $accountId = $this->params()->fromQuery('accountId');
        $courierInstance = $this->caModuleAccountService->getCourierInstanceForChannel($channelName, LocalAuthInterface::class);

        $fields = $courierInstance->getCredentialsFields();
        $fieldValues = [];
        if ($accountId) {
            $fieldValues = $this->caModuleAccountService->getCredentialsArrayForAccount($accountId);
        }

        $this->prepareAdapterImplementationFields($fields, $fieldValues);

        $saveRoute = implode('/', [Module::ROUTE, static::ROUTE, static::ROUTE_SAVE]);

        return $this->getAdapterFieldsView(
            $fields,
            $channelName,
            $saveRoute,
            'Saving credentials',
            'Credentials saved',
            $accountId
        );
    }

    public function requestCredentialsAction()
    {
        $channelName = $this->params('channel');
        $courierInstance = $this->caModuleAccountService->getCourierInstanceForChannel($channelName, CredentialRequestInterface::class);

        $instructions = $courierInstance->getCredentialsRequestInstructions();
        $fields = $courierInstance->getCredentialsRequestFields();
        $this->prepareAdapterImplementationFields($fields);

        $saveRoute = implode('/', [CAAccountSetup::ROUTE, CAAccountSetup::ROUTE_REQUEST, static::ROUTE_REQUEST_SEND]);

        $view = $this->getAdapterFieldsView(
            $fields,
            $channelName,
            $saveRoute,
            'Submitting request',
            'Request Submitted'
        );
        $view->setVariable('instructions', $instructions);

        $linkButton = $view->getChildrenByCaptureTo('linkAccount')[0];
        $linkButton->setVariable('value', 'Submit Request');

        return $view;
    }

    protected function getAdapterFieldsView(
        array $fields,
        $channelName,
        $saveRoute,
        $savingNotification = null,
        $savedNotification = null,
        $accountId = null
    ) {
        $goBackUrl = $this->plugin('url')->fromRoute($this->getAccountRoute(), ['type' => ChannelType::SHIPPING]);
        $saveUrl = $this->url()->fromRoute($saveRoute, ['channel' => $channelName]);
        if ($accountId) {
            $goBackUrl .= '/' . $accountId;
            $saveUrl .= '?accountId=' . $accountId;
        }

        $view = $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'accountId' => $accountId,
            'channelName' => $channelName,
            'saveUrl' => $saveUrl,
            'goBackUrl' => $goBackUrl,
            'fields' => $fields,
            'savingNotification' => $savingNotification,
            'savedNotification' => $savedNotification,
        ]);
        $view
            ->addChild($this->getButtonView('linkAccount', 'Link Account'), 'linkAccount')
            ->addChild($this->getButtonView('goBack', 'Go Back'), 'goBack');

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

    public function sendCredentialsRequestAction()
    {
        $channelName = $this->params('channel');
        $params = $this->getSanitisedPostParams();
        $courierInstance = $this->caModuleAccountService->getCourierInstanceForChannel($channelName, CredentialRequestInterface::class);

        $fields = $courierInstance->getCredentialsRequestFields();
        $this->prepareAdapterImplementationFields($fields, $params);

        $courierInstance->submitCredentialsRequestFields($fields);

        $view = $this->jsonModelFactory->newInstance();
        $url = $this->connectAccountAndGetRedirectUrl($params);
        $view->setVariable('redirectUrl', $url);
        return $view;
    }

    protected function getSanitisedPostParams()
    {
       // ZF2 replaces spaces in param names with underscores, need to undo that
        $params = [];
        foreach ($this->params()->fromPost() as $key => $value) {
            $params[str_replace('_', ' ', $key)] = $value;
        }
        return $params;
    }

    protected function connectAccountAndGetRedirectUrl(array $params)
    {
        $accountEntity = $this->accountCreationService->connectAccount(
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            (isset($params['account']) ? $params['account'] : null),
            $params
        );
        $url = $this->plugin('url')->fromRoute($this->getAccountRoute(), ["account" => $accountEntity->getId(), "type" => ChannelType::SHIPPING]);
        $url .= '/' . $accountEntity->getId();
        return $url;
    }

    public function saveAction()
    {
        $params = $this->getSanitisedPostParams();
        $channelName = $params['channel'];
        $courierInstance = $this->caModuleAccountService->getCourierInstanceForChannel($channelName, LocalAuthInterface::class);
        $view = $this->jsonModelFactory->newInstance();

        $fields = $courierInstance->getCredentialsFields();

        $valid = $this->caModuleAccountService->validateSetupFields($fields, $params, $courierInstance);
        if (!$valid) {
            throw new ValidationException('The entered credentials are invalid or incomplete. Please check them and try again.');
        }

        $url = $this->connectAccountAndGetRedirectUrl($params);
        $view->setVariable('redirectUrl', $url);
        return $view;
    }

    public function saveConfigAction()
    {
        $params = $this->getSanitisedPostParams();
        $accountId = $params['accountId'];
        $this->caModuleAccountService->saveConfigForAccount($accountId, $params);

        return $this->jsonModelFactory->newInstance([
            'valid' => true,
            'status' => $this->translate('Changes Saved')
        ]);
    }

    public function authSuccessAction()
    {
        $channelName = $this->params('channel');
        $accountId = $this->params()->fromQuery('accountId');
        $params = $this->params()->fromPost();

        $params['channel'] = $channelName;
        if ($accountId) {
            $params['account'] = $accountId;
        }

        try {
            $url = $this->connectAccountAndGetRedirectUrl($params);
            $this->redirect()->toUrl($url);
        } catch (InvalidCredentialsException $e) {
            $this->redirect()->toRoute(CAAccountSetup::ROUTE . '/' . CAAccountSetup::ROUTE_AUTH_FAILURE, ['channel' => $channelName]);
            return;
        }
    }

    public function authFailureAction()
    {
        $this->redirect()->toRoute($this->getAccountRoute(), ['type' => ChannelType::SHIPPING]);
    }

    public function downloadTestPackFileAction()
    {
        $accountId = $this->params()->fromQuery('accountId');
        $fileReference = $this->params()->fromQuery('file');

        $dataUri = $this->caModuleAccountService->generateTestPackFileDataForAccount($accountId, $fileReference);

        list($type, $data) = explode(';', $dataUri);
        list($encoding, $data) = explode(',', $data);
        if ($encoding == 'base64') {
            $data = base64_decode($data);
        }
        $mimeType = preg_replace('/^data:/', '', $type);
        list(, $fileExt) = explode('/', $mimeType);

        return new FileResponse($mimeType, $fileReference . '.' . $fileExt, $data);
    }

    protected function setAccountCreationService(AccountCreationService $accountCreationService)
    {
        $this->accountCreationService = $accountCreationService;
        return $this;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setCaModuleAccountService(CAModuleAccountService $caModuleAccountService)
    {
        $this->caModuleAccountService = $caModuleAccountService;
        return $this;
    }
}