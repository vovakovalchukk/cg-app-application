<?php
namespace Settings\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG\Listing\Client\Service as ListingService;
use Settings\Module;
use Zend\View\Model\ViewModel;

class EbayController extends ChannelControllerAbstract implements AddChannelSpecificVariablesToViewInterface
{
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
        $view->setVariable('shouldShowListingDefaults', $shouldShowListingDefaults);
    }
}