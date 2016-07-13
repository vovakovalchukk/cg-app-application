<?php
namespace Settings\Controller\CourierAdapter;

use CG\Account\Shared\Entity as Account;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Provider\Adapter\Service as AdapterService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ProviderController extends AbstractActionController
{
    /** @var AdapterService */
    protected $adapterService;

    public function __construct(AdapterService $adapterService)
    {
        $this->setAdapterService($adapterService);
    }

    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        $courierInterface = $this->adapterService->getAdapterCourierInterfaceForAccount($account);
        if ($account->getPending() && $courierInterface instanceof CredentialRequestInterface) {
            $pendingInstructions = $courierInterface->getAccountPendingInstructions();
            $view->setVariable('accountPendingInstructions', $pendingInstructions);
        }
    }

    protected function setAdapterService(AdapterService $adapterService)
    {
        $this->adapterService = $adapterService;
        return $this;
    }
}