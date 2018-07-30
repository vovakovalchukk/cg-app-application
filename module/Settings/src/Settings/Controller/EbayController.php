<?php
namespace Settings\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Account\CreationServiceAbstract as AccountCreationService;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG\Ebay\CodeType\ListingDuration;
use CG\Ebay\CodeType\PaymentMethod;
use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Products\Listing\Channel\Service as ListingChannelService;
use Settings\Module;
use Zend\View\Model\ViewModel;

class EbayController extends ChannelControllerAbstract implements AddChannelSpecificVariablesToViewInterface
{
    const DEFAULT_DURATION = ListingDuration::GTC;
    const DEFAULT_DISPATCH_DAYS = 3;

    /** @var ListingChannelService */
    protected $listingChannelService;
    /** @var AccountService */
    protected $accountService;

    public function __construct(
        AccountCreationService $accountCreationService,
        ActiveUserInterface $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        FeatureFlagsService $featureFlagsService,
        OrganisationUnitService $organisationUnitService,
        ListingChannelService $listingChannelService,
        AccountService $accountService
    ) {
        parent::__construct(
            $accountCreationService,
            $activeUserContainer,
            $jsonModelFactory,
            $viewModelFactory,
            $featureFlagsService,
            $organisationUnitService
        );
        $this->listingChannelService = $listingChannelService;
        $this->accountService = $accountService;
    }

    public function saveAction()
    {
        $accountEntity = $this->getAccountCreationService()->connectAccount(
            $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
            $this->params()->fromQuery('accountId'),
            $this->params()->fromQuery()
        );
        $routeName = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]);
        $url = $this->plugin('url')->fromRoute($routeName, ["account" => $accountEntity->getId(), "type" => ChannelType::SALES]);
        $this->plugin('redirect')->toUrl($url);
        return false;
    }

    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        if (!$account->getExternalDataByKey('listingDispatchTime')) {
            $account->setExternalDataByKey('listingDispatchTime', static::DEFAULT_DISPATCH_DAYS);
        }
        if (!$account->getExternalDataByKey('listingLocation')) {
            /** @var \CG\OrganisationUnit\Entity $ou */
            $ou = $this->organisationUnitService->fetch($account->getOrganisationUnitId());
            $account->setExternalDataByKey('listingLocation', $ou->getAddressCity());
        }

        $view
            ->addChild($this->getListingDurationView($account->getExternalDataByKey('listingDuration')), 'listingDurationSelect')
            ->addChild($this->getPaymentMethodView($account->getExternalDataByKey('listingPaymentMethods')), 'paymentMethodsSelect');
    }

    public function saveOAuthAction()
    {
        var_dump($this->params()->fromQuery());
        die;
        echo $this->params()->fromQuery('code') . PHP_EOL . $this->params()->fromQuery('expires_in');
        die;
    }

    public function checkOAuthAction()
    {
        $accountId = $this->params()->fromRoute('accountId');
        try {
            /** @var Account $account */
            $account = $this->accountService->fetch($accountId);
            $accountData = $this->listingChannelService->getAccountData($account);
            return $this->jsonModelFactory->newInstance([
                'listingsAuthActive' => $accountData['listingsAuthActive'] ?? false
            ]);
        } catch (NotFound $e) {
            return $this->jsonModelFactory->newInstance([
                'error' => true,
                'message' => 'There is no account with the ID ' . $accountId
            ]);
        }
    }

    protected function getListingDurationView(?string $selected = null): ViewModel
    {
        $selected = $selected ?? static::DEFAULT_DURATION;
        $options = [];
        foreach (ListingDuration::getValidValues() as $code => $text) {
            $options[] = ['title' => $text, 'value' => $code, 'selected' => $code == $selected];
        }
        return $this->getSelectView(
            'ebayAccountListingDefaultDuration',
            'listingDuration',
            $options,
            true
        );
    }

    protected function getPaymentMethodView(array $selected = []): ViewModel
    {
        $options = [];
        foreach (PaymentMethod::getStandardValues() as $code => $text) {
            $options[] = ['title' => $text, 'value' => $code, 'selected' => in_array($code, $selected)];
        }
        return $this->getMultiSelectView(
            'ebayAccountListingPaymentMethod',
            'listingPaymentMethods',
            $options,
            'None selected'
        );
    }

    protected function getSelectView(string $id, string $name, array $options, bool $required = false): ViewModel
    {
        $select = $this->getBaseSelectView($id, $name, $options, $required);
        $select->setTemplate('elements/custom-select.mustache');
        return $select;
    }

    protected function getMultiSelectView(string $id, string $name, array $options, ?string $emptyTitle = null): ViewModel
    {
        $select = $this->getBaseSelectView($id, $name, $options);
        $select->setVariable('emptyTitle', $emptyTitle);
        $select->setTemplate('elements/custom-select-group.mustache');
        return $select;
    }

    protected function getBaseSelectView(string $id, string $name, array $options, bool $required = false): ViewModel
    {
        return $this->viewModelFactory->newInstance([
            'id' => $id,
            'name' => $name,
            'options' => $options,
            'class' => $required ? 'required' : null,
        ]);
    }
}