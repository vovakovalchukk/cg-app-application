<?php
namespace SetupWizard\Payment;

use CG\Billing\Licence\Entity as Licence;
use CG\Billing\Package\Collection;
use CG\Billing\Package\Entity as Package;
use CG\Billing\Package\Filter;
use CG\Billing\Package\Service;
use CG\Billing\PricingScheme\PricingScheme;
use CG\Billing\PricingSchemeAssignment\Entity as PricingSchemeAssignment;
use CG\Billing\PricingSchemeAssignment\Service as PricingSchemeAssignmentService;
use CG\Currency\Formatter as CurrencyFormatter;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class PackageService
{
    /** @var Service */
    protected $service;
    /** @var ActiveUserInterface */
    protected $activeUser;
    /** @var PricingSchemeAssignmentService */
    protected $pricingSchemeAssignmentService;
    /** @var CurrencyFormatter */
    protected $currencyFormatter;

    public function __construct(
        Service $service,
        ActiveUserInterface $activeUser,
        PricingSchemeAssignmentService $pricingSchemeAssignmentService
    ) {
        $this->service = $service;
        $this->activeUser = $activeUser;
        $this->pricingSchemeAssignmentService = $pricingSchemeAssignmentService;
        $this->currencyFormatter = new CurrencyFormatter($this->activeUser);
    }

    /**
     * @return Package[]
     */
    public function getSelectablePackages(int $pricingSchemeId = null): Collection
    {
        $filter = (new Filter('all', 1))
            ->setPricingSchemeId([$pricingSchemeId ?? $this->getActiveUserPricingSchemeId()])
            ->setSelectable(true);

        try {
            return $this->service->fetchCollectionByFilter($filter);
        } catch (NotFound $exception) {
            return new Collection(Package::class, 'fetchCollectionByFilter', $filter->toArray());
        }
    }

    /**
     * @return Package[]
     */
    public function getSelectableOrderPackages(int $pricingSchemeId = null): Collection
    {
        $packages = $this->getSelectablePackages($pricingSchemeId);
        $orderPackages = new Collection(
            Package::class,
            $packages->getSourceDescription(),
            array_merge(
                $packages->getSourceFilters(),
                ['licenceType' => [Licence::TYPE_ORDER]]
            )
        );

        foreach ($packages as $package) {
            if (!$package->containsLicenceType(Licence::TYPE_ORDER)) {
                continue;
            }
            $orderPackages->attach($package);
        }

        return $orderPackages;
    }

    protected function getActiveUserPricingSchemeId(): int
    {
        try {
            /** @var PricingSchemeAssignment $pricingSchemeAssignment */
            $pricingSchemeAssignment = $this->pricingSchemeAssignmentService->fetch(
                $this->activeUser->getActiveUserRootOrganisationUnitId()
            );
            return $pricingSchemeAssignment->getPricingSchemeId();
        } catch(NotFound $exception) {
            return PricingScheme::SCHEME_DEFAULT;
        }
    }

    public function getPackagePrice(Package $package): string
    {
        return $this->currencyFormatter->format($package->getPrice(), null, false);
    }
}