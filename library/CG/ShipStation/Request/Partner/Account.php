<?php
namespace CG\ShipStation\Request\Partner;

use CG\ShipStation\EntityTrait\UserDetailsTrait;
use CG\ShipStation\Request\PartnerRequestAbstract;
use CG\ShipStation\Response\Partner\Account as Response;

class Account extends PartnerRequestAbstract
{
    use UserDetailsTrait;

    const METHOD = 'POST';
    const URI = '/accounts';

    /** @var  string */
    protected $externalAccountId;

    public function __construct(string $firstName, string $lastName, string $companyName, string $externalAccountId = null)
    {
        $this->setFirstName($firstName)
            ->setLastName($lastName)
            ->setCompanyName($companyName)
            ->setExternalAccountId($externalAccountId);
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'company_name' => $this->getCompanyName(),
            'external_account_id' => $this->getExternalAccountId()
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
}
