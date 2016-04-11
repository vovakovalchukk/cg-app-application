<?php
namespace Settings\Controller;

use CG\Account\CreationServiceAbstract as AccountCreationService;
use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Amazon\Credentials;
use CG\Amazon\Message\AccountAddressGenerator;
use CG\Amazon\RegionAbstract as Region;
use CG\Amazon\RegionFactory;
use CG\Channel\Type as ChannelType;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Exception;
use Settings\Module;
use Zend\View\Model\ViewModel;

class AmazonController extends ChannelControllerAbstract
{
    /** @var AccountAddressGenerator $accountAddressGenerator */
    protected $accountAddressGenerator;
    /** @var Cryptor $cryptor */
    protected $cryptor;
    /** @var RegionFactory $regionFactory */
    protected $regionFactory;

    const AMAZON_LOGISTICS_TERMS_AND_CONDITIONS_LINK = 'https://sellercentral.%s/gp/shipping-manager/terms-and-conditions.html';

    public function __construct(
        AccountCreationService $accountCreationService,
        ActiveUserInterface $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        AccountAddressGenerator $accountAddressGenerator,
        Cryptor $cryptor,
        RegionFactory $regionFactory
    ) {
        parent::__construct($accountCreationService, $activeUserContainer, $jsonModelFactory, $viewModelFactory);
        $this
            ->setAccountAddressGenerator($accountAddressGenerator)
            ->setCryptor($cryptor)
            ->setRegionFactory($regionFactory);
    }

    public function saveAction()
    {
        $accountEntity = $this->getAccountCreationService()->connectAccount(
            $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
            $this->params()->fromQuery('accountId'),
            array_merge($this->params()->fromPost(), $this->params()->fromRoute())
        );
        $routeName = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]);
        $url = $this->plugin('url')->fromRoute($routeName, ["account" => $accountEntity->getId(), "type" => ChannelType::SALES]);
        $this->plugin('redirect')->toUrl($url);
        return false;
    }

    public function addAccountsChannelSpecificVariablesToChannelSpecificView(Account $account, ViewModel $view)
    {
        $view->setVariables(
            [
                'termsAndConditionsLink' => $this->getAmazonLogisticsTermsAndConditionsLinkForAccount($account),
                'messagesAddress' => $this->getAddressForAccount($account),
            ]
        );
    }

    protected function getAmazonLogisticsTermsAndConditionsLinkForAccount(Account $account)
    {
        try {
            /** @var Credentials $credentials */
            $credentials = $this->cryptor->decrypt($account->getCredentials());
            /** @var Region $region */
            $region = $this->regionFactory->getByRegionCode($credentials->getRegionCode());

            $domains = $region->getDomains();
            if (empty($domains)) {
                return null;
            }

            return sprintf(static::AMAZON_LOGISTICS_TERMS_AND_CONDITIONS_LINK, reset($domains));
        } catch (Exception $exception) {
            return null;
        }
    }

    protected function getAddressForAccount(Account $account)
    {
        $addressGenerator = $this->accountAddressGenerator;
        return $addressGenerator($account);
    }

    /**
     * @return self
     */
    protected function setAccountAddressGenerator(AccountAddressGenerator $accountAddressGenerator)
    {
        $this->accountAddressGenerator = $accountAddressGenerator;
        return $this;
    }

    /**
     * @return self
     */
    protected function setCryptor(Cryptor $cryptor)
    {
        $this->cryptor = $cryptor;
        return $this;
    }

    /**
     * @return self
     */
    protected function setRegionFactory(RegionFactory $regionFactory)
    {
        $this->regionFactory = $regionFactory;
        return $this;
    }
}