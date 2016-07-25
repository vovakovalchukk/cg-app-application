<?php
namespace SetupWizard\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
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

    public function __construct(
        SetupService $setupService,
        ViewModelFactory $viewModelFactory,
        MessagesService $messagesService
    ) {
        $this->setSetupService($setupService)
            ->setViewModelFactory($viewModelFactory)
            ->setMessagesService($messagesService);
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

        $view->setVariable('emailInvoiceDispatchToggleValue', $this->messagesService->getEmailInvoiceOnDispatchToggleValue());
        
        foreach ($this->messagesService->fetchAccountsForActiveUser() as $account) {
            if ($account->getChannel() !== 'amazon') {
                continue;
            }

            $section = $this->messagesService->getAccountBadgeSection($account);
            $view->addChild($section, 'accountBadges', true);
        }

        return $this->setupService->getSetupView('Set Up Customer Messages', $view);
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
}