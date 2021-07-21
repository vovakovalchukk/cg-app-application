<?php
namespace CG\UkMail\CustomsDeclaration\Factory;

use CG\UkMail\CustomsDeclaration\FactoryInterface;
use CG\UkMail\CustomsDeclaration\MapperInterface;
use CG\UkMail\CustomsDeclaration\Declaration\Basic as BasicDeclaration;
use CG\UkMail\CustomsDeclaration\Mapper\Basic as BasicMapper;

class Basic implements FactoryInterface
{
    public function getDeclaration(): string
    {
        return BasicDeclaration::class;
    }

    public function getMapper(): MapperInterface
    {
        return new BasicMapper();
    }
}