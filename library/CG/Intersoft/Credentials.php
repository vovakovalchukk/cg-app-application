<?php
namespace CG\Intersoft;

class Credentials
{
    /** @var string */
    protected $applicationId;
    /** @var int */
    protected $postingLocationNumber;
    /** @var string */
    protected $userId;
    /** @var string */
    protected $password;

    public function __construct(string $applicationId, int $postingLocationNumber, string $userId, string $password)
    {
        $this->applicationId = $applicationId;
        $this->postingLocationNumber = $postingLocationNumber;
        $this->userId = $userId;
        $this->password = $password;
    }

    public static function fromArray(array $credentials): Credentials
    {
        return new static(
            $credentials['applicationId'],
            $credentials['postingLocationNumber'],
            $credentials['userId'],
            $credentials['password']
        );
    }

    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    public function getPostingLocationNumber(): int
    {
        return $this->postingLocationNumber;
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