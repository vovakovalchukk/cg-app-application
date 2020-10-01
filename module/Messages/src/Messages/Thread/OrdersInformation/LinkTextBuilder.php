<?php
namespace Messages\Thread\OrdersInformation;

use CG\Account\Shared\Entity as Account;
use CG\Communication\Thread\Entity as Thread;

class LinkTextBuilder
{
    public const LINK_TEXT_PLACEHOLDER = 'Loading order count...';
    protected const NO_ORDERS = 'No';
    protected const LINK_TEXT_FORMAT = '%s %sorder%s from %s';
    protected const ORDERS_SINGULAR_SUFFIX = '';
    protected const ORDERS_PLURAL_SUFFIX = 's';
    protected const ORDERS_DESCRIPTOR_DEFAULT = '';
    protected const ORDERS_DESCRIPTOR_RECENT = 'recent ';

    public function __invoke(Account $account, string $threadName, int $orderCount, bool $includeCount): string
    {
        if (!$includeCount) {
            return static::LINK_TEXT_PLACEHOLDER;
        }
        $ordersDescriptor = $this->getOrdersDescriptor($account);
        $ordersPlurality = $this->getOrdersPlurality($orderCount);
        return sprintf(
            static::LINK_TEXT_FORMAT,
            $orderCount > 0 ? $orderCount : static::NO_ORDERS,
            $ordersDescriptor,
            $ordersPlurality,
            $threadName
        );
    }

    protected function getOrdersDescriptor(Account $account): string
    {
        if ($account->getChannel() == Thread::CHANNEL_AMAZON) {
            return static::ORDERS_DESCRIPTOR_RECENT;
        }
        return static::ORDERS_DESCRIPTOR_DEFAULT;
    }

    protected function getOrdersPlurality(string $orderCount): string
    {
        if ($orderCount == 1) {
            return static::ORDERS_SINGULAR_SUFFIX;
        }
        return static::ORDERS_PLURAL_SUFFIX;
    }
}