<?php
namespace CG\UkMail\Response\Rest;

interface ResponseInterface
{
    public static function createFromArray(array $response);
}