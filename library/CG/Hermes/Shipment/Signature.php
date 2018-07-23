<?php
namespace CG\Hermes\Shipment;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\AddressInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\Shipment\SupportedField\SignatureRequiredInterface;
use CG\Hermes\ShipmentAbstract;

class Signature extends ShipmentAbstract implements SignatureRequiredInterface
{
    /** @var bool */
    protected $signatureRequired;

    public function __construct(
        string $customerReference,
        Account $account,
        AddressInterface $deliveryAddress,
        string $deliveryInstructions,
        DeliveryServiceInterface $deliveryService,
        bool $signatureRequired
    ) {
        parent::__construct($customerReference, $account, $deliveryAddress, $deliveryInstructions, $deliveryService);
        $this->signatureRequired = $signatureRequired;
    }

    public static function fromArray(array $array): ShipmentAbstract
    {
        return new static(
            $array['customerReference'],
            $array['account'],
            $array['deliveryAddress'],
            $array['deliveryInstructions'],
            $array['deliveryService'],
            $array['signatureRequired']
        );
    }

    /**
     * @inheritdoc
     */
    public function isSignatureRequired()
    {
        return $this->signatureRequired;
    }
}