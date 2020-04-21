<?php
namespace SetupWizard;

use CG\Access\Strategy\Factory as AccessStrategyFactory;
use CG\Access\StrategyInterface as AccessStrategyInterface;
use CG\Billing\Subscription\Collection as Subscriptions;
use CG\Billing\Subscription\Entity as Subscription;
use CG\Billing\Subscription\Filter as SubscriptionFilter;
use CG\Billing\Subscription\Service as SubscriptionService;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Access\Entity as OrganisationUnitAccess;
use CG\OrganisationUnit\Access\Service as OrganisationUnitAccessService;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\Entity as User;
use Zend\Di\Di;

class CompletionService implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'SetupCompletionService';
    protected const MAX_SAVE_ATTEMPTS = 3;

    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var OrganisationUnitAccessService */
    protected $organisationUnitAccessService;
    /** @var SubscriptionService */
    protected $subscriptionService;
    /** @var AccessStrategyFactory */
    protected $accessStrategyFactory;

    public function __construct(
        OrganisationUnitService $organisationUnitService,
        OrganisationUnitAccessService $organisationUnitAccessService,
        SubscriptionService $subscriptionService,
        AccessStrategyFactory $accessStrategyFactory
    ) {
        $this->organisationUnitService = $organisationUnitService;
        $this->organisationUnitAccessService = $organisationUnitAccessService;
        $this->subscriptionService = $subscriptionService;
        $this->accessStrategyFactory = $accessStrategyFactory;
    }

    public function completeSetup(User $user)
    {
        $organisationUnit = $this->organisationUnitService->fetch($user->getOrganisationUnitId());
        if ($this->isSetupAlreadyCompleted($organisationUnit)) {
            $this->logDebug('Setup already completed for OU %s', [$organisationUnit->getId()], static::LOG_CODE);
            return;
        }
        $this->createAccess($organisationUnit);
        $this->endSubscriptions($organisationUnit);
        $this->markSetupCompleted($organisationUnit);
    }

    protected function isSetupAlreadyCompleted(OrganisationUnit $organisationUnit): bool
    {
        $setupCompletionDate = $organisationUnit->getMetaData()->getSetupCompleteDate();
        if ($setupCompletionDate === null) {
            return false;
        }
        return new DateTime($setupCompletionDate) <= new DateTime();
    }

    protected function createAccess(OrganisationUnit $organisationUnit): void
    {
        $access = $this->organisationUnitAccessService->fetch($organisationUnit->getRoot());
        try {
            $accessStrategy = $this->createAccessStrategy($organisationUnit);
            $access
                ->setSystem($accessStrategy->hasSystemAccess() ? OrganisationUnitAccess::SYSTEM_ON : OrganisationUnitAccess::SYSTEM_RESTRICTED)
                ->setApi($accessStrategy->hasApiCredentialsAccess())
                ->setListings($accessStrategy->hasListingsAccess());
        } catch (NotFound $e) {
            $this->logWarning('No subscription found for OU %s', ['organisationUnit' => $organisationUnit->getRoot()], static::LOG_CODE);
        } finally {
            $this->organisationUnitAccessService->save($access);
        }
    }

    protected function endSubscriptions(OrganisationUnit $organisationUnit): void
    {
        try {
            $subscriptions = $this->fetchAllActiveSubscriptions($organisationUnit);
            foreach ($subscriptions as $subscription) {
                /** @var Subscription $subscription */
                $this->endSubscription($subscription);
            }
        } catch (NotFound $e) {
            return;
        }
    }

    protected function fetchAllActiveSubscriptions(OrganisationUnit $organisationUnit): Subscriptions
    {
        return $this->subscriptionService->fetchCollectionByFilter(
            (new SubscriptionFilter('all', 1))
                ->setOuId($organisationUnit->getRoot())
                ->setActive(true)
        );
    }

    protected function endSubscription(Subscription $subscription): void
    {
        $endTime = (new DateTime())->format(DateTime::FORMAT);
        for ($attempt = 1; $attempt <= static::MAX_SAVE_ATTEMPTS; $attempt++) {
            try {
                $subscription->setToDate($endTime);
                $this->subscriptionService->save($subscription);
                return;
            } catch (NotModified $e) {
                return;
            } catch (Conflict $e) {
                $this->logDebug('Conflict on attempt %s of ending subscription %s for OU %s', [$attempt, 'subscription'=> $subscription, 'ou' => $subscription->getOrganisationUnitId()], static::LOG_CODE);
                if ($attempt == static::MAX_SAVE_ATTEMPTS) {
                    $this->logError('Couldn\'t end subscription %s for OU %s after %s attempts', ['subscription'=> $subscription, 'ou' => $subscription->getOrganisationUnitId(), static::MAX_SAVE_ATTEMPTS], static::LOG_CODE);
                    return;
                }
                $subscription = $this->subscriptionService->fetch($subscription->getId());
            }
        }
    }

    protected function markSetupCompleted(OrganisationUnit $organisationUnit): void
    {
        for ($attempt = 1; $attempt <= static::MAX_SAVE_ATTEMPTS; $attempt++) {
            try {
                $organisationUnit->getMetaData()->setSetupCompleteDate((new DateTime())->format(DateTime::FORMAT));
                // ensure billing type is Manual going forwards
                $organisationUnit->setBillingType(OrganisationUnit::BILLING_TYPE_MANUAL);
                $this->organisationUnitService->save($organisationUnit);
                return;
            } catch (NotModified $e) {
                return;
            } catch (Conflict $e) {
                $this->logDebug('Conflict on attempt %s of updating OU %s', [$attempt, 'ou' => $organisationUnit->getId()], static::LOG_CODE);
                if ($attempt == static::MAX_SAVE_ATTEMPTS) {
                    $this->logError('Couldn\'t update OU %s after %s attempts', ['ou' => $organisationUnit->getId(), static::MAX_SAVE_ATTEMPTS], static::LOG_CODE);
                    return;
                }
                $organisationUnit = $this->organisationUnitService->fetch($organisationUnit->getId());
            }
        }
    }

    protected function createAccessStrategy(OrganisationUnit $organisationUnit): AccessStrategyInterface
    {
        /*
         * We are forcing the factory to create a Cg access strategy in order to determine access
         * based on the temporary subscription created during the Payment stage of setup
         */
        $organisationUnit->setBillingType(OrganisationUnit::BILLING_TYPE_CG);
        return ($this->accessStrategyFactory)($organisationUnit);
    }
}