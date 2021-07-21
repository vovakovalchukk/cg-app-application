<?php
namespace CG\UkMail\CustomsDeclaration\Factory;

use CG\UkMail\CustomsDeclaration\FactoryInterface;
use CG\UkMail\CustomsDeclaration\MapperInterface;
use CG\UkMail\CustomsDeclaration\Declaration\Full as FullDeclaration;
use CG\UkMail\CustomsDeclaration\Mapper\Full as FullMapper;

class Full implements FactoryInterface
{
    public function getDeclaration(): string
    {
        return FullDeclaration::class;
    }

    public function getMapper(): MapperInterface
    {
        return new FullMapper();
    }
}