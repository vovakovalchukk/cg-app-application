<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG\Ebay\Account\CreationService as EbayCreationService;
use CG\Ebay\CodeType\ListingDuration;
use CG\Ebay\CodeType\PaymentMethod;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\Module as ProductsModule;
use Settings\Module;
use Zend\View\Model\ViewModel;

/**
 * Class EbayController
 * @method EbayCreationService getAccountCreationService()
 * @package Settings\Controller
 */
class EbayController extends ChannelControllerAbstract implements AddChannelSpecificVariablesToViewInterface
{
    const DEFAULT_DURATION = ListingDuration::GTC;
    const DEFAULT_DISPATCH_DAYS = 3;

    public function saveAction()
    {
        $accountEntity = $this->getAccountCreationService()->connectAccount(
            $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
            $this->params()->fromQuery('accountId'),
            $this->params()->fromQuery()
        );

        try {
            $url = $this->partnerAuthoriseService->fetchPartnerSuccessRedirectUrlFromSession($accountEntity);
        } catch (NotFound $e) {
            $routeName = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]);
            $url = $this->plugin('url')->fromRoute($routeName, ["account" => $accountEntity->getId(), "type" => ChannelType::SALES]);
        }

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
        $authCode = $this->params()->fromQuery('code');
        $accountId = (int) base64_decode(urldecode($this->params()->fromQuery('state', '')));
        $this->getAccountCreationService()->saveOAuthToken($authCode, $accountId);

        $url = $this->plugin('url')->fromRoute(ProductsModule::ROUTE);
        $this->plugin('redirect')->toUrl($url);
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