<?php
namespace CG\ShipStation\Messages;

class User
{
    /** @var  string */
    protected $firstName;
    /** @var  string */
    protected $lastName;
    /** @var  string */
    protected $companyName;

    public function __construct(string $firstName, string $lastName, string $companyName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->companyName = $companyName;
    }

    public static function fromArray(array $array): User
    {
        return new static(
            $array['first_name'] ?? $array['first name'],
            $array['last_name'] ?? $array['last name'],
            $array['company'] ?? $array['company name']
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'company' => $this->getCompanyName(),
        ];
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName)
    {
        $this->companyName = $companyName;
        return $this;
    }
}
