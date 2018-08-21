<?php
namespace CG\ShipStation\ShipStation;

class ChannelGrabberCredentials extends Credentials
{
    const API_KEY = 'YpHrlVAcnJElA55pwm05fRmY6YYjA2w1Yiad+nNXVZw';

    public function __construct()
    {
        parent::__construct(static::API_KEY);
    }
}