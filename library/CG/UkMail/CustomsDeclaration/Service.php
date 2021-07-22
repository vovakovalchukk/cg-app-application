<?php
namespace CG\UkMail\CustomsDeclaration;

use CG\UkMail\Shipment;

class Service
{
    public const DECLARATION_TYPE_BASIC = 'basic';
    public const DECLARATION_TYPE_FULL = 'full';

    /** @var Factory */
    protected $abstractFactory;

    public function __construct(Factory $abstractFactory)
    {
        $this->abstractFactory = $abstractFactory;
    }

    public function getCustomsDeclaration(Shipment $shipment, string $type): CustomsDeclarationInterface
    {
        $factory = $this->createCustomsDeclaration($type);
        $mapped = $factory->getMapper()->toArray($shipment);
        /** @var CustomsDeclarationInterface $customsDeclaration */
        $customsDeclaration = ($factory->getDeclaration());
        return $customsDeclaration::fromArray($mapped);
    }

    protected function createCustomsDeclaration(string $type): FactoryInterface
    {
        return ($this->abstractFactory)($type);
    }

}