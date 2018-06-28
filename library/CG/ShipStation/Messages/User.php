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
    /** @var  string|null */
    protected $title;

    public function __construct(string $firstName, string $lastName, string $companyName, ?string $title = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->companyName = $companyName;
        $this->title = $title;
    }

    public static function fromArray(array $array): User
    {
        return new static(
            $array['first_name'] ?? $array['first name'],
            $array['last_name'] ?? $array['last name'],
            $array['company'] ?? $array['company name'],
            $array['title'] ?? null
        );
    }

    public function toArray(): array
    {
        $array = [
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'company' => $this->getCompanyName(),
        ];
        if ($this->getTitle()) {
            $array['title'] = $this->getTitle();
        }
        return $array;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): User
    {
        $this->title = $title;
        return $this;
    }
}
