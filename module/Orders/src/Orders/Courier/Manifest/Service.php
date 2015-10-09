<?php
namespace Orders\Courier\Manifest;

use CG\Account\Client\Service as AccountService;
use CG\Dataplug\Carrier\Service as CarrierService;
use CG\Dataplug\Manifest\Service as DataplugManifestService;
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

    public function __construct(
        AccountService $accountService,
        UserOUService $userOuService,
        CarrierService $carrierService,
        DataplugManifestService $dataplugManifestService
    ) {
        $this->setAccountService($accountService)
            ->setUserOuService($userOuService)
            ->setCarrierService($carrierService)
            ->setDataplugManifestService($dataplugManifestService);
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