<?php
namespace CG\RoyalMailApi\Client;

use DateTime;

class AuthToken
{
    // Its 4 hours but store for slightly less to be safe
    const DEFAULT_DURATION = '3 hours 59 minutes';

    /** @var string */
    protected $token;
    /** @var DateTime */
    protected $expires;

    public function __construct(string $token, DateTime $expires)
    {
        $this->token = $token;
        $this->expires = $expires;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpires(): DateTime
    {
        return $this->expires;
    }
}