<?php
namespace CG\Intersoft\RoyalMail\Request;

use CG\Intersoft\RequestAbstract;

abstract class PostAbstract extends RequestAbstract
{
    public function getMethod(): string
    {
        return 'POST';
    }
}