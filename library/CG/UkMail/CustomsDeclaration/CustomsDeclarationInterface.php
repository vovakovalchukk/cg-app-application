<?php
namespace CG\UkMail\CustomsDeclaration;

interface CustomsDeclarationInterface
{
    public static function fromArray(array $array): CustomsDeclarationInterface;
    public function toArray(): array;
}