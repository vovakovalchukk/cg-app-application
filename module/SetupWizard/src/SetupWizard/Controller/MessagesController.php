<?php
namespace SetupWizard\Controller;

use CG\Account\Shared\Entity as Account;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\InvoiceController as InvoiceSettingsController;
use Settings\Module as SettingsModule;
use SetupWizard\Channels\Service as ChannelService;
use SetupWizard\Controller\Service as SetupService;
use SetupWizard\Messages\Service as MessagesService;
use Zend\Mvc\Controller\AbstractActionController;

class MessagesController extends AbstractActionController
{
    const ROUTE_MESSAGE = 'Messages';

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
        /**
         * Get flag value for Email Invoice on Dispatch
         *
         *  For each amazon account
         *      Check if we have messaging setup for this account
         *      if we do then show the forwarding form
         *      else show the add messaging form
         *
         *
         *  POST to /amazon/account/save
         *  with variable originalEmailAddress as the forwarding email address
         */
        $view = $this->viewModelFactory->newInstance()->setTemplate('setup-wizard/messages/index');

        $invoiceSettings = $this->messagesService->fetchInvoiceSettings();
        $view->setVariable('emailInvoiceDispatchToggleValue', $invoiceSettings->getAutoEmail());
        $view->setVariable('emailInvoiceDispatchToggleETag', $invoiceSettings->getStoredETag());

        $saveEmailInvoicesUrl = $this->url()->fromRoute(
            SettingsModule::ROUTE . '/' . InvoiceSettingsController::ROUTE . '/' . InvoiceSettingsController::ROUTE_MAPPING . '/' . InvoiceSettingsController::ROUTE_SAVE
        );
        $view->setVariable('saveEmailInvoicesUrl', $saveEmailInvoicesUrl);
        
        foreach ($this->messagesService->fetchAmazonAccountsForActiveUser() as $account) {
            $section = $this->getSectionViewForAccount($account);
            $view->addChild($section, 'accountSections', true);
        }

        return $this->setupService->getSetupView('Set Up Customer Messages', $view);
    }

    protected function getSectionViewForAccount(Account $account)
    {
        //$setupFlag = $account->isMessagingSetup();

        $sectionView = $this->viewModelFactory->newInstance([
            'setupFlag' => true,
        ]);
        $sectionView->addChild($this->getAccountBadge($account), 'badge');
        $sectionView->setTemplate('setup-wizard/messages/accountSection');

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

    public function addMessagingAction()
    {
        /**
         * Generate the email address for this account
         */
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