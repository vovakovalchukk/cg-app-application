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
