<?php
namespace Messages\Thread\OrdersInformation;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Communication\Thread\Entity as Thread;
use CG\Order\Shared\CustomerCounts\Service as CustomerCountService;
use Messages\Thread\OrdersInformation;
use Orders\Module as OrdersModule;
use Zend\View\Helper\Url as UrlHelper;

class Factory
{
    protected const ORDER_COUNT_DEFAULT = 0;
    protected const ORDERS_MESSAGE_DEFAULT = '%s order%s from %s';
    protected const ORDERS_MESSAGE_AMAZON = '%s recent order%s from %s';
    protected const SEARCH_FIELDS = [
        'order.externalUsername',
        'billing.emailAddress',
        'shipping.emailAddress',
    ];

    /** @var AccountService */
    protected $accountService;
    /** @var CustomerCountService */
    protected $customerCountService;
    /** @var LinkTextBuilder */
    protected $linkTextBuilder;
    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(
        AccountService $accountService,
        CustomerCountService $customerCountService,
        LinkTextBuilder $linkTextBuilder,
        UrlHelper $urlHelper
    ) {
        $this->accountService = $accountService;
        $this->customerCountService = $customerCountService;
        $this->linkTextBuilder = $linkTextBuilder;
        $this->urlHelper = $urlHelper;
    }

    public function fromThread(Thread $thread, bool $includeCount = false): OrdersInformation
    {
        $account = $this->fetchAccount($thread);
        $externalUsername = $this->sanitiseThreadExternalUsername($thread, $account);
        $orderCount = $includeCount ? $this->getOrderCount($externalUsername, $account) : static::ORDER_COUNT_DEFAULT;
        $linkText = ($this->linkTextBuilder)($account, $thread->getName(), $orderCount, $includeCount);
        $ordersUrl = $this->getOrdersUrl($externalUsername, $thread);
        return new OrdersInformation(
            $orderCount,
            $linkText,
            $ordersUrl,
            $account->getDisplayName()
        );
    }

    protected function getOrderCount(string $externalUsername, Account $account): int
    {
        $rootOuId = $account->getRootOrganisationUnitId();
        if ($this->forceRefetch($account, $externalUsername)) {
            $this->customerCountService->remove($rootOuId, $externalUsername);
        }
        return $this->customerCountService->fetch($rootOuId, $externalUsername);
    }

    protected function getOrdersUrl(string $externalUsername, Thread $thread): string
    {
        return ($this->urlHelper)(
            OrdersModule::ROUTE,
            [],
            [
                'query' => [
                    'search' => $externalUsername,
                    'searchField' => static::SEARCH_FIELDS,
                    'archived' => true,
                ],
            ]
        );
    }

    protected function fetchAccount(Thread $thread): Account
    {
        return $this->accountService->fetch($thread->getAccountId());
    }

    protected function sanitiseThreadExternalUsername(Thread $thread, Account $account): string
    {
        $externalUsername = $thread->getExternalUsername();
        if ($account->getChannel() !== 'amazon') {
            return $externalUsername;
        }
        $externalUsername = preg_replace('/(\+[A-z0-9]+)/', '', $externalUsername);
        return $externalUsername;
    }

    protected function forceRefetch(Account $account, string $externalUsername): bool
    {
        if ($account->getChannel() == Thread::CHANNEL_AMAZON) {
            return $this->customerCountService->fetch($account->getRootOrganisationUnitId(), $externalUsername) <= 0;
        }
        return false;
    }
}