<?php
namespace CG\UkMail\Response\Soap;

use CG\UkMail\Response\ResponseInterface;
use CG\UkMail\Response\AbstractSoapResponse;

class CancelConsignment extends AbstractSoapResponse implements ResponseInterface
{

    public static function createResponse($response): ResponseInterface
    {
        print_r($response);

        return new static();
        // TODO: Implement createResponse() method.
    }
}