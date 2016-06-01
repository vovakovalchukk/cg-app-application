<?php
namespace SetupWizard\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Channel\Service as SettingsChannelService;
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

    public function __construct(
        SetupService $setupService,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        ChannelsService $channelsService,
        SettingsChannelService $settingsChannelService
    ) {
        $this->setSetupService($setupService)
            ->setViewModelFactory($viewModelFactory)
            ->setJsonModelFactory($jsonModelFactory)
            ->setChannelsService($channelsService)
            ->setSettingsChannelService($settingsChannelService);
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/channels/index');
        $view->setVariable('pickUri', $this->url()->fromRoute(Module::ROUTE . '/' . static::ROUTE_CHANNELS . '/' . static::ROUTE_CHANNEL_PICK));

        $this->addAccountAddButtonToView($view)
            ->addExistingAccountsToView($view);

        return $this->setupService->getSetupView('Add Channels', $view);
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
            'name' => $account->getDisplayName(),
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
        foreach ($channelOptions as $description => $details)
        {
            $channel = $details['channel'];
            $region = (isset($details['region']) ? $details['region'] : null);
            $this->addChannelOptionToView($view, $channel, $description, $region);
        }

        return $this;
    }

    protected function addChannelOptionToView(ViewModel $view, $channel, $description, $region = null)
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
            'name' => $description,
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
        $region = $this->params()->fromPost('region');
        $type = ChannelType::SALES;

        $returnRoute = Module::ROUTE . '/' . static::ROUTE_CHANNELS;
        $this->channelsService->storeAddChannelReturnRoute($returnRoute);

        $redirectUrl = $this->settingsChannelService->createAccount($type, $channel, $region);
        return $this->jsonModelFactory->newInstance(['url' => $redirectUrl]);
    }

    protected function setSetupService(SetupService $setupService)
    {
        $this->setupService = $setupService;
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

    protected function setChannelsService(ChannelsService $channelsService)
    {
        $this->channelsService = $channelsService;
        return $this;
    }

    protected function setSettingsChannelService(SettingsChannelService $settingsChannelService)
    {
        $this->settingsChannelService = $settingsChannelService;
        return $this;
    }
}