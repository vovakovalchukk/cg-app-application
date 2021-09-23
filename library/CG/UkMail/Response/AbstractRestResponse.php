<?php
namespace CG\UkMail\Response;

abstract class AbstractRestResponse
{
    public static function isRestResponse(): bool
    {
        return true;
    }
}