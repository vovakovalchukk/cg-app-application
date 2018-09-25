<?php
namespace ShipStation\Account\ChannelSpecificVariables;

use ShipStation\Account\ChannelSpecificVariablesInterface;
use Zend\View\Model\ViewModel;

class Other implements ChannelSpecificVariablesInterface
{
    public function __invoke(): ?ViewModel
    {
        // No-op. Most couriers don't need anything additional.
        return null;
    }
}