<?php
namespace Orders\Courier\Manifest;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Manifest\Mapper as AccountManifestMapper;
use CG\Account\Shared\Manifest\Service as AccountManifestService;
use CG\Account\Shared\Manifest\Status as AccountManifestStatus;
use CG\Dataplug\Carrier\Service as CarrierService;
use CG\Dataplug\Manifest\Service as DataplugManifestService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\User\OrganisationUnit\Service as UserOUService;
use Orders\Courier\GetShippingAccountsTrait;
use Orders\Courier\GetShippingAccountOptionsTrait;

class Service
{
    use GetShippingAccountsTrait;
    use GetShippingAccountOptionsTrait;

    /** @var AccountService */
    protected $accountService;
    /** @var UserOUService */
    protected $userOuService;
    /** @var CarrierService */
    protected $carrierService;
    /** @var DataplugManifestService */
    protected $dataplugManifestService;
    /** @var AccountManifestMapper */
    protected $accountManifestMapper;
    /** @var AccountManifestService */
    protected $accountManifestService;

    public function __construct(
        AccountService $accountService,
        UserOUService $userOuService,
        CarrierService $carrierService,
        DataplugManifestService $dataplugManifestService,
        AccountManifestMapper $accountManifestMapper,
        AccountManifestService $accountManifestService
    ) {
        $this->setAccountService($accountService)
            ->setUserOuService($userOuService)
            ->setCarrierService($carrierService)
            ->setDataplugManifestService($dataplugManifestService)
            ->setAccountManifestMapper($accountManifestMapper)
            ->setAccountManifestService($accountManifestService);
    }

    /**
     * @return array ['openOrders' => int, 'oncePerDay' => bool, 'manifestedToday' => bool]
     */
    public function getDetailsForShippingAccount($accountId)
    {
        $account = $this->accountService->fetch($accountId);
        $carrier = $this->carrierService->getCarrierForAccount($account);

        $dataplugManifests = $this->dataplugManifestService->retrieveDataplugManifestsForAccount($account);
        $openOrders = $this->dataplugManifestService->getOpenOrderCountFromRetrieveResponse($dataplugManifests);
        $latestManifestDate = $this->dataplugManifestService->getLatestManifestDateFromRetrieveResponse($dataplugManifests);

        return [
            'openOrders' => $openOrders,
            'oncePerDay' => $carrier->getManifestOncePerDay(),
            'manifestedToday' => ($latestManifestDate != null && date('Y-m-d', strtotime($latestManifestDate)) == date('Y-m-d')),
        ];
    }

    /**
     * @return string PDF manifest data
     */
    public function generateManifestForShippingAccount($accountId)
    {
        $account = $this->accountService->fetch($accountId);
        $accountManifest = $this->createAccountManifest($account);
// TODO: deal with potential ManifestMissingException by catching and creating a Gearman job to try again
        $pdfData = $this->dataplugManifestService->createManifestForAccount($account, $accountManifest);
        return base64_decode($pdfData);
    }

    protected function createAccountManifest(Account $account)
    {
        $now = new StdlibDateTime();
        $accountManifest = $this->accountManifestMapper->fromArray([
            'organisationUnitId' => $account->getOrganisationUnitId(),
            'accountId' => $account->getId(),
            'status' => AccountManifestStatus::CREATING,
            'created' => $now->stdFormat(),
        ]);
        $hal = $this->accountManifestService->save($accountManifest);
        return $this->accountManifestMapper->fromHal($hal);
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function setUserOuService(UserOUService $userOuService)
    {
        $this->userOuService = $userOuService;
        return $this;
    }

    protected function setCarrierService(CarrierService $carrierService)
    {
        $this->carrierService = $carrierService;
        return $this;
    }

    protected function setDataplugManifestService(DataplugManifestService $dataplugManifestService)
    {
        $this->dataplugManifestService = $dataplugManifestService;
        return $this;
    }

    protected function setAccountManifestMapper(AccountManifestMapper $accountManifestMapper)
    {
        $this->accountManifestMapper = $accountManifestMapper;
        return $this;
    }

    protected function setAccountManifestService(AccountManifestService $accountManifestService)
    {
        $this->accountManifestService = $accountManifestService;
        return $this;
    }

    // Required by GetShippingAccountsTrait
    protected function getAccountService()
    {
        return $this->accountService;
    }
    protected function getUserOuService()
    {
        return $this->userOuService;
    }
}