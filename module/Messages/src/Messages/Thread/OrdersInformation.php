<?php
namespace Messages\Thread;

class OrdersInformation
{
    /** @var ?string */
    protected $count;
    /** @var ?string */
    protected $linkText;
    /** @var ?string */
    protected $ordersUrl;
    /** @var ?string */
    protected $accountName;

    public function __construct(
        ?string $count = null,
        ?string $linkText = null,
        ?string $ordersUrl = null,
        ?string $accountName = null
    ) {
        $this->count = $count;
        $this->linkText = $linkText;
        $this->ordersUrl = $ordersUrl;
        $this->accountName = $accountName;
    }

    public static function fromArray(array $data): OrdersInformation
    {
        return new static(
            $data['count'] ?? null,
            $data['linkText'] ?? null,
            $data['ordersUrl'] ?? null,
            $data['accountName'] ?? null
        );
    }

    public function getCount(): ?string
    {
        return $this->count;
    }

    public function getLinkText(): ?string
    {
        return $this->linkText;
    }

    public function getOrdersUrl(): ?string
    {
        return $this->ordersUrl;
    }

    public function getAccountName(): ?string
    {
        return $this->accountName;
    }
}