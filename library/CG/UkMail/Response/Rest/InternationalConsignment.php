<?php
namespace CG\UkMail\Response\Rest;

use CG\UkMail\Response\AbstractRestResponse;
use CG\UkMail\Response\ResponseInterface;

class InternationalConsignment extends AbstractRestResponse implements ResponseInterface
{
    public function __construct()
    {

    }

    public static function createResponse($response): ResponseInterface
    {
        print_r($response);

        return new static();
    }
}