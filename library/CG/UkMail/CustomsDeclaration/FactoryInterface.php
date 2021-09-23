<?php
namespace CG\UkMail\CustomsDeclaration;

interface FactoryInterface
{
    public function getDeclaration(): string;
    public function getMapper(): MapperInterface;
}