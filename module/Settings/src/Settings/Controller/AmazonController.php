<?php
namespace Settings\Controller;

use CG\Account\CreationServiceAbstract as AccountCreationService;
use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as Account;
use CG\Amazon\Credentials;
use CG\Amazon\Gearman\WorkerFunction\GetHistoricalFulfilledShipmentsData;
use CG\Amazon\Gearman\Workload\GetHistoricalFulfilledShipmentsData as GetHistoricalFulfilledShipmentsDataWorkload;
use CG\Amazon\Message\AccountAddressGenerator;
use CG\Amazon\RegionAbstract as Region;
use CG\Amazon\RegionFactory;
use CG\Channel\Type as ChannelType;
use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Exception;
use GearmanClient;
use Partner\Account\AuthoriseService as PartnerAuthoriseService;
use Settings\Module;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AmazonController extends ChannelControllerAbstract implements
    AccountActiveToggledInterface,
    AddChannelSpecificVariablesToViewInterface
{
    /** @var AccountAddressGenerator $accountAddressGenerator */
    protected $accountAddressGenerator;
    /** @var Cryptor $cryptor */
    protected $cryptor;
    /** @var RegionFactory $regionFactory */
    protected $regionFactory;
    /** @var GearmanClient */
    protected $gearmanClient;

    const AMAZON_LOGISTICS_TERMS_AND_CONDITIONS_LINK = 'https://sellercentral.%s/gp/shipping-manager/terms-and-conditions.html';

    public function __construct(
        AccountCreationService $accountCreationService,
        ActiveUserInterface $activeUserContainer,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        FeatureFlagsService $featureFlagsService,
        OrganisationUnitService $organisationUnitService,
        PartnerAuthoriseService $partnerAuthoriseService,
        AccountAddressGenerator $accountAddressGenerator,
        Cryptor $cryptor,
        RegionFactory $regionFactory,
        GearmanClient $gearmanClient
    ) {
        parent::__construct(
            $accountCreationService, $activeUserContainer, $jsonModelFactory, $viewModelFactory, $featureFlagsService, $organisationUnitService, $partnerAuthoriseService
        );
        $this
            ->setAccountAddressGenerator($accountAddressGenerator)
            ->setCryptor($cryptor)
            ->setRegionFactory($regionFactory);
        $this->gearmanClient = $gearmanClient;
    }

    public function saveAction()
    {
        $accountEntity = $this->getAccountCreationService()->connectAccount(
            $this->getActiveUserContainer()->getActiveUser()->getOrganisationUnitId(),
            $this->params()->fromQuery('accountId'),
            array_merge($this->params()->fromPost(), $this->params()->fromRoute())
        );

        try {
            $url = $this->partnerAuthoriseService->fetchPartnerSuccessRedirectUrlFromSession($accountEntity);
        } catch (NotFound $exception) {
            $routeName = implode('/', [Module::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]);
            $url = $this->plugin('url')->fromRoute($routeName, ["account" => $accountEntity->getId(), "type" => ChannelType::SALES]);
        }

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

    public function accountActiveToggled(Account $account, JsonModel $response)
    {
        if (!$account->getActive()) {
            return;
        }
        if ($account->getExternalData()['fbaOrderImport']) {
            $historicalFulfilledShipmentsWorkload = new GetHistoricalFulfilledShipmentsDataWorkload($account->getId());
            $this->gearmanClient->doBackground(
                GetHistoricalFulfilledShipmentsData::FUNCTION_NAME,
                serialize($historicalFulfilledShipmentsWorkload),
                GetHistoricalFulfilledShipmentsData::FUNCTION_NAME . '-' . $account->getId()
            );
        }
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