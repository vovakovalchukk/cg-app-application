<?php
namespace CourierAdapter\Controller;

use CG\Channel\Type as ChannelType;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Provider\Account as CAAccountService;
use CG\CourierAdapter\Provider\Adapter\Service as AdapterService;
use CG\CourierAdapter\Provider\Account\CreationService as AccountCreationService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\User\ActiveUserInterface;
use InvalidArgumentException;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Form\Element as ZendFormElement;
use Zend\Mvc\Controller\AbstractActionController;

class AccountController extends AbstractActionController
{
    const ROUTE = 'Account';
    const ROUTE_SAVE = 'Save';
    const ROUTE_REQUEST_SEND = 'Send';

    /** @var AdapterService */
    protected $adapterService;
    /** @var AccountCreationService */
    protected $accountCreationService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(
        AdapterService $adapterService,
        AccountCreationService $accountCreationService,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->setAdapterService($adapterService)
            ->setAccountCreationService($accountCreationService)
            ->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setActiveUserContainer($activeUserContainer);
    }

    public function setupAction()
    {
        // TODO
    }

    public function requestCredentialsAction()
    {
        $channelName = $this->params('channel');
        $courierInterface = $this->getCredentialsRequestInterfaceForChannel($channelName);

        $instructions = $courierInterface->getCredentialsRequestInstructions();
        $fields = $courierInterface->getCredentialsRequestFields();
        $this->prepareRequestCredentialsFields($fields);

        $goBackUrl = $this->plugin('url')->fromRoute($this->getAccountRoute(), ['type' => ChannelType::SHIPPING]);
        $saveRoute = implode('/', [CAAccountService::ROUTE, CAAccountService::ROUTE_REQUEST, static::ROUTE_REQUEST_SEND]);
        $saveUrl = $this->url()->fromRoute($saveRoute, ['channel' => $channelName]);

        $view = $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'channelName' => $channelName,
            'saveUrl' => $saveUrl,
            'goBackUrl' => $goBackUrl,
            'instructions' => $instructions,
            'fields' => $fields,
        ]);
        $view
            ->addChild($this->getButtonView('linkAccount', 'Link Account'), 'linkAccount')
            ->addChild($this->getButtonView('goBack', 'Go Back'), 'goBack');

        return $view;
    }

    protected function getCredentialsRequestInterfaceForChannel($channelName)
    {
        if (!$this->adapterService->isProvidedChannel($channelName)) {
            throw new InvalidArgumentException(__METHOD__ . ' called with channel ' . $channelName . ' but that is not a channel provided by the Courier Adapters');
        }
        $adapter = $this->adapterService->getAdapterByChannelName($channelName);
        $courierInterface = $this->adapterService->getAdapterCourierInterface($adapter);
        if (!$courierInterface instanceof CredentialRequestInterface) {
            throw new InvalidArgumentException(__METHOD__ . ' called with channel ' . $channelName . ' but its adapter does not implement ' . CredentialRequestInterface::class);
        }
        return $courierInterface;
    }

    protected function prepareRequestCredentialsFields(array $fields, array $values = [])
    {
        foreach ($fields as $field) {
            if (!$field instanceof ZendFormElement) {
                throw new InvalidArgumentException('Form elements must be instances of ' . ZendFormElement::class);
            }
            if ($field->getOption('required')) {
                $class = $field->getAttribute('class') ?: '';
                $field->setAttribute('class', $class . ' required');
            }
            if (isset($values[$field->getName()])) {
                $field->setValue($values[$field->getName()]);
            }
        }
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
        $params = $this->params()->fromPost();
        $courierInterface = $this->getCredentialsRequestInterfaceForChannel($channelName);

        $fields = $courierInterface->getCredentialsRequestFields();
        $this->prepareRequestCredentialsFields($fields, $params);

        $courierInterface->submitCredentialsRequestFields($fields);

        $view = $this->jsonModelFactory->newInstance();
        $accountEntity = $this->accountCreationService->connectAccount(
            $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            $params['account'],
            $params
        );
        $url = $this->plugin('url')->fromRoute($this->getAccountRoute(), ["account" => $accountEntity->getId(), "type" => ChannelType::SHIPPING]);
        $url .= '/' . $accountEntity->getId();
        $view->setVariable('redirectUrl', $url);
        return $view;
    }

    public function authSuccessAction()
    {
        // TODO
    }

    public function authFailureAction()
    {
        // TODO
    }

    protected function setAdapterService(AdapterService $adapterService)
    {
        $this->adapterService = $adapterService;
        return $this;
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
}