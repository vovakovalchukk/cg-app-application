<?php
namespace ShipStation\Setup;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG\ShipStation\Account\CreationService as AccountCreationService;
use CG\ShipStation\Carrier\Entity as Carrier;
use CG\ShipStation\Credentials;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use ShipStation\SetupInterface;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\View\Model\ViewModel;

class Usps implements SetupInterface
{
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var Redirect */
    protected $redirectHelper;
    /** @var AccountCreationService */
    protected $accountCreationService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        Redirect $redirectHelper,
        AccountCreationService $accountCreationService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->redirectHelper = $redirectHelper;
        // This is a specially configured version for USPS, see ShipStation/config/module.config.php
        $this->accountCreationService = $accountCreationService;
    }


    public function __invoke(
        Carrier $carrier,
        int $organisationUnitId,
        Account $account = null,
        Credentials $credentials = null
    ): ViewModel {
        $savedAccount = $this->accountCreationService->connectAccount(
            $organisationUnitId,
            $account ? $account->getId() : null,
            ['channel' => $carrier->getChannelName()]
        );
        $this->redirectHelper->toRoute($this->getAccountRoute(), ['account' => $savedAccount->getId(), 'type' => ChannelType::SHIPPING]);
        return $this->viewModelFactory->newInstance();
    }

    protected function getAccountRoute(): string
    {
        return implode('/', [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]);
    }
}