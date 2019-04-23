<?php
namespace Partner\Account;

use Throwable;

class InvalidRequestException extends \Exception
{
    /** @var string */
    protected $redirectUrl;

    public function __construct(string $redirectUrl, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->redirectUrl = $redirectUrl;
        parent::__construct($message, $code, $previous);
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
