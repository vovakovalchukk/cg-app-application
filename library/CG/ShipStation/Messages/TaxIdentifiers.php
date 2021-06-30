<?php
namespace CG\ShipStation\Messages;

use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\Courier\Label\OrderItemsData;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Messages\TaxIdentifiers\TaxIdentifier;

class TaxIdentifiers
{
    /** @var TaxIdentifier[] */
    protected $taxIdentifiers;

    public function __construct(array $taxIdentifiers = [])
    {
        $this->taxIdentifiers = $taxIdentifiers;
    }

    public static function createFromOrder(
        Order $order,
        OrderData $orderData,
        OrganisationUnit $rootOu
    ): self {
        $taxIdentifiers = new static();
        if ($order->getIossNumber() !== null) {
            $taxIdentifiers->addTaxIdentifier(TaxIdentifier::createIossNumber($order, $rootOu));
            return $taxIdentifiers;
        }
        if ($orderData->getEoriNumber() !== null) {
            $taxIdentifiers->addTaxIdentifier(TaxIdentifier::createEoriNumber($orderData, $rootOu));
            return $taxIdentifiers;
        }
        return $taxIdentifiers;
    }

    public function toArray(): array
    {
        return array_map(
            function (TaxIdentifier $taxIdentifier) {
                return $taxIdentifier->toArray();
            },
            $this->getTaxIdentifiers()
        );
    }

    public function getTaxIdentifiers(): array
    {
        return $this->taxIdentifiers;
    }

    public function addTaxIdentifier(TaxIdentifier $taxIdentifier): self
    {
        $this->taxIdentifiers[] = $taxIdentifier;
        return $this;
    }
}