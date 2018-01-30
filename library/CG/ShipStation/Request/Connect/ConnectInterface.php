<?php
namespace CG\ShipStation\Request\Connect;

interface ConnectInterface
{
    public static function fromArray(array $params): ConnectInterface;
}
