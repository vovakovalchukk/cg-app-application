<?php
namespace CG\UkMail\Request\Rest;

use CG\UkMail\Request\RestAbstract;
use CG\UkMail\RequestInterface;
use CG\UkMail\Response\Rest\Authenticate as Response;

class Authenticate extends RestAbstract implements RequestInterface
{
    protected const METHOD = 'GET';
    protected const URI = '/gateway/SSOAuthenticationAPI/1.0/ssoAuth/users/authenticate';

    public function getMethod(): string
    {
        return static::METHOD;
    }

    public function getUri(): string
    {
        return static::URI;
    }

    public function asJson(): string
    {
        return '';
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }
}