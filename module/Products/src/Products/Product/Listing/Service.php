<?php
namespace Products\Product\Listing;

use CG\Billing\Licence\Entity as Licence;
use CG\Billing\Subscription\Service as SubscriptionService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Sites;
use CG\User\ActiveUserInterface;

class Service
{
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var SubscriptionService */
    protected $subscriptionService;
    /** @var Sites */
    protected $sites;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        SubscriptionService $subscriptionService,
        Sites $sites
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->subscriptionService = $subscriptionService;
        $this->sites = $sites;
    }

    public function isListingCreationAllowed(): bool
    {
        try {
            $this->subscriptionService->getCurrentPackageForOuId(
                $this->activeUserContainer->getActiveUserRootOrganisationUnitId(), Licence::TYPE_LISTING
            );
            return true;
        } catch (NotFound $e) {
            return false;
        }
    }

    public function getManagePackageUrl(): string
    {
        return $this->sites->host('admin') . '/billing/package';
    }
}