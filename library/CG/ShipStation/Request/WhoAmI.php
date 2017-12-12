<?php
namespace CG\ShipStation\Request;

use CG\ShipStation\Response\WhoAmI as Response;

class WhoAmI extends PartnerRequestAbstract
{
    const URI = '/whoami';
    const METHOD = 'GET';

    public function toArray(): array
    {
        return [];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }
}
