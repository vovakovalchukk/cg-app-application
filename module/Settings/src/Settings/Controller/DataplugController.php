<?php
namespace Settings\Controller;

use CG\Account\CreationServiceAbstract as AccountCreationService;
use CG\Account\Shared\Entity as Account;
use CG\Dataplug\Carrier\Service as CarrierService;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\ChannelControllerAbstract;
use Zend\View\Model\ViewModel;

class DataplugController extends ChannelControllerAbstract
{
    /** @var CarrierService */
    protected $carrierService;

    public function __construct(
        AccountCreationService $accountCreationService,
        ActiveUserInterface $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        CarrierService $carrierService
    ) {
        parent::__construct($accountCreationService, $activeUserContainer, $jsonModelFactory, $viewModelFactory);
        $this->setCarrierService($carrierService);
    }

    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        $carrier = $this->carrierService->getCarrierForAccount($account);
        $view->setVariable('carrier', $carrier);
        $automaticManifestTime = $account->getExternalData()['automaticManifestTime'];
        $timeOptions = [];
        for ($hour = 0; $hour < 24; $hour++) {
            foreach ([0, 15, 30, 45] as $minute) {
                $time = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minute, 2, '0', STR_PAD_LEFT);
                $timeOptions[] = [
                    'value' => $time . ':00',
                    'title' => $time,
                    'selected' => ($time . ':00' == $automaticManifestTime),
                ];
            }
        }
        $view->setVariable('timeOptions', $timeOptions);
    }

    protected function setCarrierService(CarrierService $carrierService)
    {
        $this->carrierService = $carrierService;
        return $this;
    }
}