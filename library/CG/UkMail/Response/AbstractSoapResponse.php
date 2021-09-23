<?php
namespace CG\UkMail\Response;

abstract class AbstractSoapResponse
{
    public static function isRestResponse(): bool
    {
        return false;
    }
}