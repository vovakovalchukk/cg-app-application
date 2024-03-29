<?php
namespace SetupWizard\Controller;

use CG\Account\Shared\Entity as Account;
use CG_Amazon\Controller\AccountController as AmazonAccountController;
use CG_Amazon\Module as AmazonModule;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Channels\Service as ChannelService;
use SetupWizard\Controller\Service as SetupService;
use SetupWizard\Messages\Service as MessagesService;
use SetupWizard\Module;
use Zend\Mvc\Controller\AbstractActionController;

class MessagesController extends AbstractActionController
{
    const ROUTE_MESSAGE = 'Messages';
    const ROUTE_SETUP = 'Setup';
    const ROUTE_SETUP_DONE = 'Done';

    /** @var SetupService */
    protected $setupService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var MessagesService */
    protected $messagesService;
    /** @var ChannelService */
    protected $channelService;

    public function __construct(
        SetupService $setupService,
        ViewModelFactory $viewModelFactory,
        MessagesService $messagesService,
        ChannelService $channelService
    ) {
        $this->setSetupService($setupService)
            ->setViewModelFactory($viewModelFactory)
            ->setMessagesService($messagesService)
            ->setChannelService($channelService);
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance()->setTemplate('setup-wizard/messages/index');

        $saveAmazonOriginalEmailUrl = $this->url()->fromRoute(AmazonModule::ROUTE . '/' . AmazonAccountController::ROUTE . '/' . AmazonAccountController::ROUTE_SAVE_ORIG_EMAIL);
        $view->setVariable('saveAmazonOriginalEmailUrl', $saveAmazonOriginalEmailUrl);

        try {
            foreach ($this->messagesService->fetchAmazonAccountsForActiveUser() as $account) {
                $section = $this->getSectionViewForAccount($account);
                $view->addChild($section, 'accountSections', true);
            }
        } catch (NotFound $ex) {
            // No-op
        }

        return $this->setupService->getSetupView('Set Up Customer Messages', $view);
    }

    protected function getSectionViewForAccount(Account $account)
    {
        $externalData = $account->getExternalData();
        $sectionView = $this->viewModelFactory->newInstance([
            'originalEmailAddress' => (isset($externalData['originalEmailAddress']) ? $externalData['originalEmailAddress'] : null),
            'setupRequired' => !(isset($externalData['messagingSetUp']) && $externalData['messagingSetUp']),
            'name' => $account->getDisplayName(),
            'id' => $account->getId(),
        ]);
        $sectionView->addChild($this->getAccountBadge($account), 'badge')
            ->addChild($this->getSetupButton($account), 'setupButton')
            ->setTemplate('setup-wizard/messages/accountSection');

        return $sectionView;
    }

    protected function getAccountBadge($account)
    {
        $img = $this->channelService->getImageFromAccount($account);
        $badgeView = $this->viewModelFactory->newInstance([
            'image' => $img,
            'id' => $account->getId(),
            'name' => $account->getDisplayName(),
        ]);
        $badgeView->setTemplate('setup-wizard/channels/account-badge.mustache');
        return $badgeView;
    }

    protected function getSetupButton(Account $account)
    {
        $url = $this->url()->fromRoute(
            Module::ROUTE . '/' . static::ROUTE_MESSAGE . '/' . static::ROUTE_SETUP, ['account' => $account->getId()]
        );
        $view = $this->viewModelFactory->newInstance([
            'buttons' => [[
                'value' => 'Add Messaging',
                'id' => 'setup-wizard-messaging-add-button-' . $account->getId(),
                'class' => 'setup-wizard-messaging-add-button',
                'disabled' => false,
                'action' => $url,
            ]]
        ]);
        $view->setTemplate('elements/buttons.mustache');
        return $view;
    }

    public function setupAction()
    {
        $accountId = $this->params()->fromRoute('account');
        $view = $this->viewModelFactory->newInstance()->setTemplate('setup-wizard/messages/setup');
        $view->setVariable('email', $this->messagesService->getEmailForAmazonAccount($accountId));
        $view->addChild($this->getAmazonSettingsButton($accountId), 'amazonSettingsButton');

        return $this->setupService->getSetupView('Add Amazon Messaging', $view, $this->getSetupFooterView($accountId));
    }

    protected function getAmazonSettingsButton($accountId)
    {
        $view = $this->viewModelFactory->newInstance([
            'buttons' => [[
                'value' => 'Amazon Settings',
                'id' => 'setup-wizard-messaging-amazon-settings-button',
                'action' => $this->messagesService->getAmazonSettingsUrlForAccount($accountId),
            ]]
        ]);
        $view->setTemplate('elements/buttons.mustache');
        return $view;
    }

    protected function getSetupFooterView($accountId)
    {
        $doneUrl = $this->url()->fromRoute(
            Module::ROUTE . '/' . static::ROUTE_MESSAGE . '/' . static::ROUTE_SETUP . '/' . static::ROUTE_SETUP_DONE, ['account' => $accountId]
        );
        $cancelUrl = $this->url()->fromRoute(Module::ROUTE . '/' . static::ROUTE_MESSAGE);
        $footer = $this->viewModelFactory->newInstance([
            'buttons' => [
                [
                    'value' => 'Done',
                    'id' => 'setup-wizard-messages-amazon-done-button',
                    'class' => 'setup-wizard-messages-amazon-button setup-wizard-done-button',
                    'action' => $doneUrl,
                ],
                [
                    'value' => 'Cancel',
                    'id' => 'setup-wizard-messages-amazon-cancel-button',
                    'class' => 'setup-wizard-messages-amazon-button setup-wizard-cancel-button',
                    'action' => $cancelUrl,
                ]
            ]
        ]);
        $footer->setTemplate('elements/buttons.mustache');
        return $footer;
    }

    public function setupDoneAction()
    {
        $accountId = $this->params()->fromRoute('account');
        $this->messagesService->markAmazonMessagingSetupDone($accountId);

        $this->redirect()->toRoute(Module::ROUTE . '/' . static::ROUTE_MESSAGE);
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
    protected function setMessagesService(MessagesService $messagesService)
    {
        $this->messagesService = $messagesService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setChannelService(ChannelService $channelService)
    {
        $this->channelService = $channelService;
        return $this;
    }
}