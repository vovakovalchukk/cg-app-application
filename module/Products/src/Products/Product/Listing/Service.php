<?php
namespace Products\Product\Listing;

use CG\Billing\Licence\Entity as Licence;
use CG\Billing\Subscription\Service as SubscriptionService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;

class Service
{
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var SubscriptionService */
    protected $subscriptionService;

    public function __construct(ActiveUserInterface $activeUserContainer, SubscriptionService $subscriptionService)
    {
        $this->activeUserContainer = $activeUserContainer;
        $this->subscriptionService = $subscriptionService;
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
}