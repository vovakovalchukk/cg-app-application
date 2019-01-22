<?php
namespace CG\ShipStation\Request\Connect;

use CG\ShipStation\Messages\ConnectAddress as Address;
use CG\ShipStation\Messages\User;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Request\Connect\Ups\Invoice;
use CG\ShipStation\Response\Connect\Response as Response;

/**
 * This is required as we have separate channel names for UPS UK and UPS US
 *
 * Class UpsUs
 * @package CG\ShipStation\Request\Connect
 */
class UpsUs extends Ups
{

}
