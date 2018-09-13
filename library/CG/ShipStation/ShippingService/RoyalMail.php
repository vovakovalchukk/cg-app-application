<?php
namespace CG\ShipStation\ShippingService;

use CG\Account\Shared\Entity as Account;

class RoyalMail extends Other implements RequiresSignatureInterface
{
    /** @var array */
    protected $signatureServices;

    public function __construct(Account $account, string $domesticCountryCode, array $signatureServices = [])
    {
        parent::__construct($account, $domesticCountryCode);
        $this->signatureServices = $signatureServices;
    }

    public function doesServiceRequireSignature(string $service): bool
    {
        return in_array($service, $this->signatureServices);
    }
}