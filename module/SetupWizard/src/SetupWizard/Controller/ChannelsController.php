<?php
namespace SetupWizard\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Integration\Type as ChannelIntegrationType;
use CG\Channel\Type as ChannelType;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Channel\Service as SettingsChannelService;
use SetupWizard\Channels\ConnectViewFactory;
use SetupWizard\Channels\Service as ChannelsService;
use SetupWizard\Controller\Service as SetupService;
use SetupWizard\Module;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ChannelsController extends AbstractActionController
{
    const ROUTE_CHANNELS = 'Channels';
    const ROUTE_CHANNEL_PICK = 'Pick';
    const ROUTE_CHANNEL_ADD = 'Add';
    const ROUTE_CHANNEL_SAVE = 'Save';
    const ROUTE_CHANNEL_DELETE = 'Delete';
    const ROUTE_CHANNEL_CONNECT = 'Connect';
    const CG_EMAIL_NOTIFICATION_INTEGRATION_TYPES = [
        ChannelIntegrationType::CLASSIC => true,
        ChannelIntegrationType::THIRD_PARTY => true,
        ChannelIntegrationType::UNSUPPORTED => true
    ];

    /** @var SetupService */
    protected $setupService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ChannelsService */
    protected $channelsService;
    /** @var SettingsChannelService */
    protected $settingsChannelService;
    /** @var ConnectViewFactory $connectViewFactory */
    protected $connectViewFactory;

    public function __construct(
        SetupService $setupService,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        ChannelsService $channelsService,
        SettingsChannelService $settingsChannelService,
        ConnectViewFactory $connectViewFactory
    ) {
        $this
            ->setSetupService($setupService)
            ->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setChannelsService($channelsService)
            ->setSettingsChannelService($settingsChannelService)
            ->setConnectViewFactory($connectViewFactory);
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/channels/index')
            ->setVariable('pickUri', $this->url()->fromRoute(Module::ROUTE . '/' . static::ROUTE_CHANNELS . '/' . static::ROUTE_CHANNEL_PICK))
            ->setVariable('saveUri', $this->url()->fromRoute(Module::ROUTE . '/' . static::ROUTE_CHANNELS . '/' . static::ROUTE_CHANNEL_SAVE))
            ->setVariable('deleteUri', $this->url()->fromRoute(Module::ROUTE . '/' . static::ROUTE_CHANNELS . '/' . static::ROUTE_CHANNEL_DELETE));

        $this->addAccountAddButtonToView($view)
            ->addExistingAccountsToView($view);

        return $this->setupService->getSetupView('Add Channels', $view, $this->getMainFooterView());
    }

    protected function addAccountAddButtonToView(ViewModel $view)
    {
        $badgeView = $this->viewModelFactory->newInstance([
            'text' => '+',
            'description' => 'Add a channel',
        ]);
        $badgeView->setTemplate('setup-wizard/channels/button-badge.mustache');
        $view->addChild($badgeView, 'addBadge');
        return $this;
    }

    protected function addExistingAccountsToView(ViewModel $view)
    {
        try {
            $accounts = $this->channelsService->fetchAccountsForActiveUser();
        } catch (NotFound $e) {
            return;
        }
        foreach ($accounts as $account) {
            $this->addExistingAccountToView($view, $account);
        }
    }

    protected function addExistingAccountToView(ViewModel $view, Account $account)
    {
        $img = $this->channelsService->getImageFromAccount($account);
        $badgeView = $this->viewModelFactory->newInstance([
            'image' => $img,
            'id' => $account->getId(),
            'name' => $account->getDisplayName(),
            'channel' => $account->getChannel(),
            'controls' => [[
                'name' => 'edit',
                'icon' => Module::PUBLIC_FOLDER . 'img/icons/edit.png',
            ],[
                'name' => 'delete',
                'icon' => Module::PUBLIC_FOLDER . 'img/icons/delete.png',
            ]]
        ]);
        $badgeView->setTemplate('setup-wizard/channels/account-badge.mustache');
        $view->addChild($badgeView, 'accountBadges', true);
        return $this;
    }

    protected function getMainFooterView()
    {
        // No skip button for this step as it must be completed
        $footer = $this->viewModelFactory->newInstance([
            'buttons' => [
                $this->setupService->getNextButtonViewConfig(),
            ]
        ]);
        $footer->setTemplate('elements/buttons.mustache');
        return $footer;
    }

    public function pickAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/channels/pick');
        $view->setVariable('addUri', $this->url()->fromRoute(Module::ROUTE . '/' . static::ROUTE_CHANNELS . '/' . static::ROUTE_CHANNEL_ADD));

        $this->addChannelOptionsToView($view);

        return $this->setupService->getSetupView('Pick a Channel', $view, $this->getPickFooterView());
    }

    protected function addChannelOptionsToView(ViewModel $view)
    {
        $channelOptions = $this->channelsService->getSalesChannelOptions();
        foreach ($channelOptions as $name => $details)
        {
            $channel = $details['channel'];
            $integrationType = (isset($details['integrationType']) ? $details['integrationType'] : null);
            $region = (isset($details['region']) ? $details['region'] : null);
            $this->addChannelOptionToView($view, $channel, $name, $integrationType, $region);
        }

        return $this;
    }

    protected function addChannelOptionToView(ViewModel $view, $channel, $name, $integrationType, $region = null)
    {
        $img = $channel . '.png';
        if ($region) {
            $img = $channel . strtoupper($region) . '.png';
        }
        $img = Module::PUBLIC_FOLDER . 'img/channel-badges/' . $img;

        $badgeView = $this->viewModelFactory->newInstance([
            'image' => $img,
            'channel' => $channel,
            'region' => $region,
            'integrationType' => $integrationType,
            'name' => $name,
        ]);
        $badgeView->setTemplate('setup-wizard/channels/channel-badge.mustache');
        $view->addChild($badgeView, 'channelBadges', true);

        return $this;
    }

    protected function getPickFooterView()
    {
        $footer = $this->viewModelFactory->newInstance([
            'buttons' => [
                [
                    'value' => 'Back',
                    'id' => 'setup-wizard-back-button',
                    'class' => 'setup-wizard-next-button setup-wizard-back-button',
                    'disabled' => false,
                    'action' => $this->url()->fromRoute(Module::ROUTE . '/' . static::ROUTE_CHANNELS),
                ]
            ]
        ]);
        $footer->setTemplate('elements/buttons.mustache');
        return $footer;
    }

    public function addAction()
    {
        $channel = $this->params()->fromPost('channel');
        $printName = $this->params()->fromPost('printName');
        $region = $this->params()->fromPost('region');
        $integrationType = $this->params()->fromPost('integrationType');
        $type = ChannelType::SALES;
        $result = ['url' => null];

        if ($integrationType == ChannelIntegrationType::INTERNAL) {
            $redirectUrl = $this->settingsChannelService->createAccount($type, $channel, $region);
            if ($this->isInternalUrl($redirectUrl)) {
                $redirectUrl = $this->constructConnectUrl($channel, $region);
            }
            $result['url'] = $redirectUrl;
        }

        if ($this->shouldEmailCGOnAdd($channel)) {
            $this->setupService->sendChannelAddNotificationEmailToCG($channel, $printName, $integrationType);
        }

        return $this->jsonModelFactory->newInstance($result);
    }

    protected function shouldEmailCGOnAdd($integrationType): bool
    {
        return isset(static::CG_EMAIL_NOTIFICATION_INTEGRATION_TYPES[$integrationType]);
    }

    protected function isInternalUrl($url)
    {
        return (substr($url, 0, 1) == '/');
    }

    protected function constructConnectUrl($channel, $region = null)
    {
        $routeParams = ['channel' => $channel];
        if ($region) {
            $routeParams['region'] = $region;
        }
        return $this->url()->fromRoute(
            Module::ROUTE . '/' . static::ROUTE_CHANNELS . '/' . static::ROUTE_CHANNEL_ADD . '/' . static::ROUTE_CHANNEL_CONNECT,
            $routeParams
        );
    }

    public function saveAction()
    {
        $data = $this->params()->fromPost();
        $id = $data['id'];
        unset($data['id']);

        $this->channelsService->updateAccount($id, $data);
        return $this->jsonModelFactory->newInstance(['success' => true]);
    }

    public function deleteAction()
    {
        $id = $this->params()->fromPost('id');
        $this->channelsService->deleteAccount($id);
        return $this->jsonModelFactory->newInstance(['success' => true]);
    }

    public function connectAction()
    {
        $channel = $this->params()->fromRoute('channel');
        $region = $this->params()->fromRoute('region');
        $displayName = $this->channelsService->getSalesChannelDisplayName($channel);

        $connectViewFactory = $this->connectViewFactory;
        return $this->setupService->getSetupView(
            'Add ' . $displayName,
            $connectViewFactory($channel, $region),
            false
        );
    }

    /**
     * @return self
     */
    protected function setSetupService(SetupService $setupService)
    {
        $this->setupService = $setupService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return self
     */
    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return self
     */
    protected function setChannelsService(ChannelsService $channelsService)
    {
        $this->channelsService = $channelsService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setSettingsChannelService(SettingsChannelService $settingsChannelService)
    {
        $this->settingsChannelService = $settingsChannelService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setConnectViewFactory(ConnectViewFactory $connectViewFactory)
    {
        $this->connectViewFactory = $connectViewFactory;
        return $this;
    }
}
