<?php
namespace CG\UkMail\Response\Rest;

use CG\UkMail\Response\AbstractRestResponse;
use CG\UkMail\Response\ResponseInterface;

class Authenticate extends AbstractRestResponse implements ResponseInterface
{
    public static function createResponse($response)
    {
        print_r($response);

        // TODO: Implement createFromArray() method.
    }
}