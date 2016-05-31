<?php
namespace SetupWizard\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Channels\Service as ChannelsService;
use SetupWizard\Controller\Service as SetupService;
use SetupWizard\Module;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ChannelsController extends AbstractActionController
{
    const ROUTE_CHANNELS = 'Channels';

    /** @var SetupService */
    protected $setupService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var ChannelsService */
    protected $channelsService;

    public function __construct(
        SetupService $setupService,
        ViewModelFactory $viewModelFactory,
        ChannelsService $channelsService
    ) {
        $this->setSetupService($setupService)
            ->setViewModelFactory($viewModelFactory)
            ->setChannelsService($channelsService);
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/channels/index');

        $this->addExistingAccountsToView($view);

        return $this->setupService->getSetupView('Add Channels', $view);
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
        $badgeView->setTemplate('setup-wizard/account-badge.mustache');
        $view->addChild($badgeView, 'accountBadges', true);
        return $this;
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

    protected function setChannelsService(ChannelsService $channelsService)
    {
        $this->channelsService = $channelsService;
        return $this;
    }
}