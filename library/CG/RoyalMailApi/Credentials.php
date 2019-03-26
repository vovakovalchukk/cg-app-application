<?php
namespace CG\RoyalMailApi;

class Credentials
{
    /** @var string */
    protected $clientId;
    /** @var string */
    protected $clientSecret;
    /** @var string */
    protected $username;
    /** @var string */
    protected $password;

    public function __construct(string $clientId, string $clientSecret, string $username, string $password)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->username = $username;
        $this->password = $password;
    }

    public static function fromArray(array $credentials): Credentials
    {
        return new static(
            $credentials['clientId'],
            $credentials['clientSecret'],
            $credentials['username'],
            $credentials['password']
        );
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}