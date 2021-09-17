<?php
namespace CG\UkMail\Response;

interface ResponseInterface
{
    public static function createResponse($response): ResponseInterface;
    public static function isRestResponse(): bool;
}