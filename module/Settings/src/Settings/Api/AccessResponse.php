<?php
namespace Settings\Api;

class AccessResponse
{
    /** @var bool */
    protected $allowed;
    /** @var string|null */
    protected $message;
    /** @var string|null */
    protected $url;

    public function __construct(bool $allowed, ?string $message = null, ?string $url = null)
    {
        $this->allowed = $allowed;
        $this->message = $message;
        $this->url = $url;
    }

    public function toArray(): array
    {
        return [
            'allowed' => $this->isAllowed(),
            'message' => $this->getMessage(),
            'url' => $this->getUrl(),
        ];
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function setAllowed(bool $allowed): AccessResponse
    {
        $this->allowed = $allowed;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): AccessResponse
    {
        $this->message = $message;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): AccessResponse
    {
        $this->url = $url;
        return $this;
    }
}