<?php
namespace CG\UkMail\CustomsDeclaration;

use CG\UkMail\Shipment;

interface MapperInterface
{
    public const INVOICE_TYPE_PROFORMA = 'proforma';

    public function toArray(Shipment $shipment): array;
}