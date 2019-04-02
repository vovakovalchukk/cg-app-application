<?php
namespace CG\Intersoft;

class Credentials
{
    /** @var string */
    protected $applicationId;
    /** @var string */
    protected $userId;
    /** @var string */
    protected $password;

    public function __construct(string $applicationId, string $userId, string $password)
    {
        $this->applicationId = $applicationId;
        $this->userId = $userId;
        $this->password = $password;
    }

    public static function fromArray(array $credentials): Credentials
    {
        return new static(
            $credentials['applicationId'],
            $credentials['userId'],
            $credentials['password']
        );
    }

    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}