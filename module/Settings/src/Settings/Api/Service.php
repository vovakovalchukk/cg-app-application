<?php
namespace Settings\Api;

use CG\ApiCredentials\Entity as ApiCredentials;
use CG\ApiCredentials\Service as ApiCredentialsService;
use CG\Billing\Licence\Entity as Licence;
use CG\Billing\Package\Collection as PackageCollection;
use CG\Billing\Package\Entity as Package;
use CG\Billing\Package\Filter as PackageFilter;
use CG_Billing\Package\ManagementService as PackageManagementService;
use CG\Billing\Package\Service as PackageService;
use CG\Billing\Subscription\Entity as Subscription;
use CG\Billing\Subscription\Service as SubscriptionService;
use CG\Locale\PhoneNumber;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Sites;
use CG\User\ActiveUserInterface as ActiveUserContainer;
use CG_UI\View\Prototyper\ViewModelFactory;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'ApiSettingsService';
    const LOG_CREDENTIALS_GEN = 'Public API credentials not found for OU %d, will generate';
    const LOG_ACCESS_DENIED = 'Public API access denied: OU %d not in UK and not on suitable Package.';

    const MIN_PACKAGE_BAND = 'Growth Accelerator (USA)';
    const MSG_UPGRADE = '<p>Open API access allows you to connect third party software to ChannelGrabber.</p><p>API access is limited to our \'%s\' package or higher. Click below to upgrade now.</p><p>Not sure? Contact our eCommerce specialists on %s to discuss or <a href="%s" target="_blank">Click Here</a> to book a demo.</p>';
    const DEMO_URL = 'https://meetings.hubspot.com/sam197/cgdemo';
    const MANAGE_PACKAGE_URI = '/billing/package';

    /** @var ActiveUserContainer */
    protected $activeUserContainer;
    /** @var ApiCredentialsService */
    protected $apiCredentialsService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var SubscriptionService */
    protected $subscriptionService;
    /** @var PackageManagementService */
    protected $packageManagementService;
    /** @var PackageService */
    protected $packageService;
    /** @var Sites */
    protected $sites;

    /** @var PackageCollection|null */
    protected $pricingSchemePackages;

    public function __construct(
        ActiveUserContainer $activeUserContainer,
        ViewModelFactory $viewModelFactory,
        ApiCredentialsService $apiCredentialsService,
        OrganisationUnitService $organisationUnitService,
        SubscriptionService $subscriptionService,
        PackageManagementService $packageManagementService,
        PackageService $packageService,
        Sites $sites
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->viewModelFactory = $viewModelFactory;
        $this->apiCredentialsService = $apiCredentialsService;
        $this->organisationUnitService = $organisationUnitService;
        $this->subscriptionService = $subscriptionService;
        $this->packageManagementService = $packageManagementService;
        $this->packageService = $packageService;
        $this->sites = $sites;
    }

    public function isAccessAllowedForActiveUser(): AccessResponse
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $rootOu = $this->organisationUnitService->fetch($rootOuId);
        $response = new AccessResponse(false);

        if ($this->isLocaleUK($rootOu)) {
            $response->setAllowed(true);
            return $response;
        }
        if (!$this->isOusCurrentPackageAllowedAccess($rootOu)) {
            $this->logNotice(static::LOG_ACCESS_DENIED, ['ou' => $rootOuId], [static::LOG_CODE, 'AccessDenied']);
            $response->setMessage($this->buildUpgradeRequiredMessage($rootOu))
                ->setUrl($this->getManagePackageUrl());
            return $response;
        }

        $response->setAllowed(true);
        return $response;
    }

    protected function isLocaleUK(OrganisationUnit $ou)
    {
        return ($ou->getLocale() == $ou::LOCALE_UK);
    }

    protected function isOusCurrentPackageAllowedAccess(OrganisationUnit $rootOu): bool
    {
        try {
            $currentPackage = $this->getCurrentPackageForOu($rootOu);
            $allowedPackages = $this->getAllowedPackagesForOu($rootOu);
            return $allowedPackages->containsId($currentPackage->getId());
        } catch (NotFound $e) {
            return false;
        }
    }

    protected function getCurrentPackageForOU(OrganisationUnit $rootOu): Package
    {
        /** @var Subscription $subscription */
        $subscription = $this->subscriptionService->fetchActiveSubscriptionForOuId($rootOu->getId());
        /** @var Package $package */
        return $subscription->getPackages()->getFirstWithLicenceType(Licence::TYPE_ORDER);
    }


    protected function getAllowedPackagesForOu(OrganisationUnit $rootOu): PackageCollection
    {
        $allPackages = $this->fetchAvailablePackagesForOu($rootOu);
        $minRequiredPackage = $this->getMinimumRequiredPackageForAccess($rootOu);
        $allowedPackages = new PackageCollection(Package::class, __FUNCTION__, ['organisationUnitId' => $rootOu->getId()]);
        $allowedPackages->attach($minRequiredPackage);
        $currentPackage = $minRequiredPackage;
        while ($currentPackage->getNext()) {
            $nextPackage = $allPackages->getById($currentPackage->getNext());
            $allowedPackages->attach($nextPackage);
            $currentPackage = $nextPackage;
        }
        return $allowedPackages;
    }

    protected function buildUpgradeRequiredMessage(OrganisationUnit $rootOu): string
    {
        $minPackage = $this->getMinimumRequiredPackageForAccess($rootOu);
        $contactNo = PhoneNumber::getForLocale($rootOu->getLocale());
        $demoUrl = static::DEMO_URL;
        return sprintf(static::MSG_UPGRADE, $minPackage->getName(), $contactNo, $demoUrl);
    }

    protected function getMinimumRequiredPackageForAccess(OrganisationUnit $rootOu): Package
    {
        $packages = $this->fetchAvailablePackagesForOu($rootOu);
        $minRequiredPackage = $packages->getBy('band', static::MIN_PACKAGE_BAND)->getFirst();
        if ($minRequiredPackage == null) {
            $exception = new \RuntimeException(
                sprintf('No Package of the minimum band %s found for OU %d', static::MIN_PACKAGE_BAND, $rootOu->getId())
            );
            $this->logException($exception, 'warning', __NAMESPACE__);
            throw $exception;
        }
        return $minRequiredPackage;
    }

    protected function fetchAvailablePackagesForOu(OrganisationUnit $rootOu): PackageCollection
    {
        if ($this->pricingSchemePackages) {
            return $this->pricingSchemePackages;
        }
        $pricingSchemeId = $this->packageManagementService->getPricingSchemeIdForActiveUser();
        $filter = new PackageFilter();
        $filter->setLimit('all')
            ->setPage(1)
            ->setPricingSchemeId([$pricingSchemeId])
            ->setSelectable(true);
        $this->pricingSchemePackages = $this->packageService->fetchCollectionByFilter($filter);
        return $this->pricingSchemePackages;
    }

    protected function getManagePackageUrl(): string
    {
        return 'https://' . $this->sites->host('admin') . static::MANAGE_PACKAGE_URI;
    }

    public function getCredentialsForActiveUser(): ApiCredentials
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        try {
            return $this->apiCredentialsService->fetch($rootOuId);
        } catch (NotFound $ex) {
            $this->logDebug(static::LOG_CREDENTIALS_GEN, [$rootOuId], static::LOG_CODE);
            return $this->generateCredentialsForActiveUser();
        }
    }

    protected function generateCredentialsForActiveUser(): ApiCredentials
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $rootOu = $this->organisationUnitService->fetch($rootOuId);
        return $this->apiCredentialsService->generateForOu($rootOu);
    }
}