<?php
namespace Partner\Notification\Request;

use Partner\Notification\RequestInterface;

class Account implements RequestInterface
{
    const METHOD = 'POST';

    /** @var string */
    protected $url;
    /** @var int */
    protected $accountId;
    /** @var int */
    protected $accountRequestId;

    public function __construct(string $url, int $accountId, int $accountRequestId)
    {
        $this->url = $url;
        $this->accountId = $accountId;
        $this->accountRequestId = $accountRequestId;
    }

    public function toArray(): array
    {
        return [
            'accountId' => $this->getAccountId(),
            'accountRequestId' => $this->getAccountRequestId()
        ];
    }

    public function getMethod(): string
    {
        return static::METHOD;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getAccountRequestId(): int
    {
        return $this->accountRequestId;
    }
}
