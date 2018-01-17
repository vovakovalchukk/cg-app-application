<?php
namespace CG\ShipStation\Request\Partner;

use CG\ShipStation\Messages\User;
use CG\ShipStation\Request\PartnerRequestAbstract;
use CG\ShipStation\Response\Partner\Account as Response;

class Account extends PartnerRequestAbstract
{
    const METHOD = 'POST';
    const URI = '/accounts';

    /** @var  User */
    protected $user;
    /** @var  string */
    protected $externalAccountId;

    public function __construct(User $user, string $externalAccountId)
    {
        $this->user = $user;
        $this->externalAccountId = $externalAccountId;
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->user->getFirstName(),
            'last_name' => $this->user->getLastName(),
            'company_name' => $this->user->getCompanyName(),
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

    public function getUser(): User
    {
        return $this->user;
    }
}
