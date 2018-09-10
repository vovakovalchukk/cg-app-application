<?php
namespace ShipStation\Account;

use Zend\View\Model\ViewModel;

interface ChannelSpecificVariablesInterface
{
    public function __invoke(): ?ViewModel;
}