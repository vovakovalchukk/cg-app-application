<?php
namespace CG\ShipStation\ShippingService;

interface RequiresSignatureInterface
{
    public function doesServiceRequireSignature(string $service): bool;
}