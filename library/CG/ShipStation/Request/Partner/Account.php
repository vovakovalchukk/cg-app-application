<?php
namespace CG\ShipStation\Request\Partner;

use CG\ShipStation\Messages\User;
use CG\ShipStation\Request\Partner\Account\ExternalIdTrait;
use CG\ShipStation\Request\PartnerRequestAbstract;
use CG\ShipStation\Response\Partner\Account as Response;

class Account extends PartnerRequestAbstract
{
    use ExternalIdTrait;

    const METHOD = 'POST';
    const URI = '/accounts';

    /** @var  User */
    protected $user;
    /** @var  string */
    protected $externalAccountId;
    /** @var string */
    protected $originCountryCode;

    public function __construct(User $user, string $externalAccountId, string $originCountryCode)
    {
        $this->user = $user;
        $this->externalAccountId = $this->generateExternalId($externalAccountId);
        $this->originCountryCode = $originCountryCode;
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->user->getFirstName(),
            'last_name' => $this->user->getLastName(),
            'company_name' => $this->user->getCompanyName(),
            'external_account_id' => $this->getExternalAccountId(),
            'origin_country_code' => $this->getOriginCountryCode(),
        ];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getExternalAccountId(): ?string
    {
        return $this->externalAccountId;
    }

    public function setExternalAccountId($externalAccountId)
    {
        $this->externalAccountId = $externalAccountId;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getOriginCountryCode(): string
    {
        return $this->originCountryCode;
    }

    public function setOriginCountryCode(string $originCountryCode): Account
    {
        $this->originCountryCode = $originCountryCode;
        return $this;
    }
}
