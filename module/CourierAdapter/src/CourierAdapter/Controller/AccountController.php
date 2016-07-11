<?php
namespace CourierAdapter\Controller;

use CG\Channel\Type as ChannelType;
use CG\CourierAdapter\Provider\Account as CAAccountService;
use CG\CourierAdapater\Account\CredentialRequestInterface;
use CG\CourierAdapter\Provider\Adapter\Service as AdapterService;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Module as SettingsModule;
use Settings\Controller\ChannelController;
use Zend\Form\Element as ZendFormElement;
use Zend\Mvc\Controller\AbstractActionController;

class AccountController extends AbstractActionController
{
    const ROUTE_REQUEST_SEND = 'Send';

    /** @var AdapterService */
    protected $adapterService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(AdapterService $adapterService, ViewModelFactory $viewModelFactory)
    {
        $this->setAdapterService($adapterService)
            ->setViewModelFactory($viewModelFactory);
    }

    public function setupAction()
    {
        // TODO
    }

    public function requestCredentialsAction()
    {
        $channelName = $this->params('channel');
        $accountId = $this->params()->fromQuery('accountId');
        if (!$this->adapterService->isProvidedChannel($channelName)) {
            throw new \InvalidArgumentException(__METHOD__ . ' called with channel ' . $channelName . ' but that is not a channel provided by the Courier Adapters');
        }
        $adapter = $this->adapterService->getAdapterByChannelName($channelName);
        $courierInterface = $this->adapterService->getAdapterCourierInterface($adapter);
        if (!$courierInterface instanceof CredentialRequestInterface) {
            throw new \InvalidArgumentException(__METHOD__ . ' called with channel ' . $channelName . ' but its adapter does not implement ' . CredentialRequestInterface::class);
        }

        $instructions = $courierInterface->getCredentialsRequestInstructions();
        $fields = $courierInterface->getCredentialsRequestFields();
        $this->prepareRequestCredentialsFields($fields);

        $goBackUrl = $this->plugin('url')->fromRoute($this->getAccountRoute(), ['type' => ChannelType::SHIPPING]);
        if ($accountId) {
            $goBackUrl .= '/' . $accountId;
        }
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

    protected function prepareRequestCredentialsFields(array $fields)
    {
        foreach ($fields as $field) {
            if (!$field instanceof ZendFormElement) {
                throw new \InvalidArgumentException('Form elements must be instances of ' . ZendFormElement::class);
            }
            if ($field->getOption('required')) {
                $class = $field->getAttribute('class') ?: '';
                $field->setAttribute('class', $class . ' required');
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

    public function sendCredentialsRequest()
    {
        // TODO
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

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }
}