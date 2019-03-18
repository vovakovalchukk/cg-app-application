<?php
namespace CG\RoyalMailApi\Request;

use CG\RoyalMailApi\Request\GetAbstract;
use CG\RoyalMailApi\Response\AuthToken as Response;

class AuthToken extends GetAbstract
{
    public function getUri(): string
    {
        return '/token';
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }
}