<?php
namespace Settings\Controller\CourierAdapter;

use CG\Account\Shared\Entity as Account;
use CG\CourierAdapter\Provider\Account as CAAccountService;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Provider\Adapter\Service as AdapterService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ProviderController extends AbstractActionController
{
    /** @var AdapterService */
    protected $adapterService;
    /** @var CAAccountService */
    protected $caAccountService;

    public function __construct(AdapterService $adapterService, CAAccountService $caAccountService)
    {
        $this->setAdapterService($adapterService)
            ->setCaAccountService($caAccountService);
    }

    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        $courierInterface = $this->adapterService->getAdapterCourierInterfaceForAccount($account);
        if ($account->getPending() && $courierInterface instanceof CredentialRequestInterface) {
            $pendingInstructions = $courierInterface->getAccountPendingInstructions();
            $view->setVariable('accountPendingInstructions', $pendingInstructions);
            return;
        }

        $setupUrl = $this->caAccountService->getInitialisationUrl($account, '');
        $view->setVariable('url', $setupUrl);

        // TODO: check for ConfigInterface and show config fields
    }

    protected function setAdapterService(AdapterService $adapterService)
    {
        $this->adapterService = $adapterService;
        return $this;
    }

    protected function setCaAccountService(CAAccountService $caAccountService)
    {
        $this->caAccountService = $caAccountService;
        return $this;
    }
}