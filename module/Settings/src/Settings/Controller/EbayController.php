<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG\Ebay\CodeType\ListingDuration;
use CG\Ebay\CodeType\PaymentMethod;
use CG\Listing\Client\Service as ListingService;
use CG\Locale\CurrencyCode;
use Settings\Module;
use Zend\View\Model\ViewModel;

class EbayController extends ChannelControllerAbstract implements AddChannelSpecificVariablesToViewInterface
{
    const DEFAULT_CURRENCY = 'GBP';
    const DEFAULT_DURATION = ListingDuration::GTC;
    const DEFAULT_DISPATCH_DAYS = 3;
    const DEFAULT_PAYMENT_METHODS = [PaymentMethod::PAY_PAL];

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
        $shouldShowListingDefaults = $this->featureFlagsService->isActive(
            ListingService::FEATURE_FLAG_CREATE_LISTINGS,
            $this->getActiveUserRootOrganisationUnit()
        );
        $view->setVariable('shouldShowListingDefaults', $shouldShowListingDefaults)
            ->setVariable('defaultListingDispatchDays', static::DEFAULT_DISPATCH_DAYS)
            ->addChild($this->getCurrencySelectView($account->getExternalDataByKey('listingCurrency')), 'currencySelect')
            ->addChild($this->getListingDurationView($account->getExternalDataByKey('listingDuration')), 'listingDurationSelect')
            ->addChild($this->getPaymentMethodView($account->getExternalDataByKey('listingPaymentMethods')), 'paymentMethodsSelect');
    }

    protected function getCurrencySelectView(?string $selected = null): ViewModel
    {
        $selected = $selected ?? static::DEFAULT_CURRENCY;
        $options = [];
        foreach (CurrencyCode::getCurrencyCodes() as $currencyCode) {
            $options[] = ['title' => $currencyCode, 'value' => $currencyCode, 'selected' => $currencyCode == $selected];
        }
        $selectView = $this->getSelectView(
            'ebayAccountListingDefaultCurrency',
            'listingCurrency',
            $options,
            $selected
        );
        $selectView->setVariable('searchField', true);
        return $selectView;
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
            $selected
        );
    }

    protected function getPaymentMethodView(array $selected = []): ViewModel
    {
        $selected = (!empty($selected) ? $selected : static::DEFAULT_PAYMENT_METHODS);
        $options = [];
        foreach (PaymentMethod::getStandardValues() as $code => $text) {
            $options[] = ['title' => $text, 'value' => $code, 'selected' => in_array($code, $selected)];
        }
        return $this->getMultiSelectView(
            'ebayAccountListingPaymentMethod',
            'listingPaymentMethods',
            $options,
            $selected
        );
    }

    protected function getSelectView(string $id, string $name, array $options): ViewModel
    {
        $select = $this->getBaseSelectView($id, $name, $options);
        $select->setTemplate('elements/custom-select.mustache');
        return $select;
    }

    protected function getMultiSelectView(string $id, string $name, array $options): ViewModel
    {
        $select = $this->getBaseSelectView($id, $name, $options);
        $select->setTemplate('elements/custom-select-group.mustache');
        return $select;
    }

    protected function getBaseSelectView(string $id, string $name, array $options): ViewModel
    {
        return $this->viewModelFactory->newInstance([
            'id' => $id,
            'name' => $name,
            'options' => $options
        ]);
    }
}