<?php
namespace SetupWizard\Payment;

use CG\Billing\Licence\Entity as Licence;
use CG\Billing\Package\Collection;
use CG\Billing\Package\Entity as Package;
use CG\Billing\Package\Filter;
use CG\Billing\Package\Service;
use CG\Billing\Price\Service as PriceService;
use CG\Billing\PricingScheme\PricingScheme;
use CG\Billing\PricingSchemeAssignment\Entity as PricingSchemeAssignment;
use CG\Billing\PricingSchemeAssignment\Service as PricingSchemeAssignmentService;
use CG\Billing\Subscription\Entity as Subscription;
use CG\Currency\Formatter as CurrencyFormatter;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG_Mustache\View\Renderer;
use CG_UI\View\Prototyper\ViewModelFactory;

class PackageService
{
    /** @var Service */
    protected $service;
    /** @var ActiveUserInterface */
    protected $activeUser;
    /** @var PricingSchemeAssignmentService */
    protected $pricingSchemeAssignmentService;
    /** @var PriceService */
    protected $priceService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var Renderer */
    protected $renderer;
    /** @var CurrencyFormatter */
    protected $currencyFormatter;

    public function __construct(
        Service $service,
        ActiveUserInterface $activeUser,
        PricingSchemeAssignmentService $pricingSchemeAssignmentService,
        PriceService $priceService,
        ViewModelFactory $viewModelFactory,
        Renderer $renderer
    ) {
        $this->service = $service;
        $this->activeUser = $activeUser;
        $this->pricingSchemeAssignmentService = $pricingSchemeAssignmentService;
        $this->priceService = $priceService;
        $this->viewModelFactory = $viewModelFactory;
        $this->renderer = $renderer;
        $this->currencyFormatter = new CurrencyFormatter($this->activeUser, null, false);
    }

    public function getLocale(): string
    {
        return $this->activeUser->getLocale();
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
            return PricingScheme::getDefaultPricingSchemeIdForLocale($this->activeUser->getLocale());
        }
    }

    public function getPackagePrice(Package $package, int $billingDuration = null): string
    {
        return $this->currencyFormatter->format(
            $this->priceService->getChargeablePackagePriceAsFloat($package, $billingDuration)
        );
    }

    public function getPackageMonthlyPrice(Package $package, int $billingDuration = null): string
    {
        $price = $this->priceService->getChargeablePackagePrice($package, $billingDuration);
        if ($price->getBillingDuration() === $price->getChargeableBillingDuration()) {
            return $this->currencyFormatter->format($price->getChargeableAmount() / $price->getBillingDuration());
        }

        $monthlyPrice = $this->viewModelFactory->newInstance([
            'fullPrice' => $this->currencyFormatter->format($price->getChargeableAmount() / $price->getChargeableBillingDuration()),
            'discountedPrice' => $this->currencyFormatter->format($price->getChargeableAmount() / $price->getBillingDuration()),
        ])->setTemplate('package/discountedPrice');

        return $this->renderer->render($monthlyPrice);
    }

    public function fetch(int $id): Package
    {
        return $this->service->fetch($id);
    }
}