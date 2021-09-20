<?php
namespace CG\UkMail\Consignment\Cancel;

use CG\UkMail\Request\Soap\CancelConsignment as CancelConsignmentRequest;
use CG\UkMail\Shipment;

class Mapper
{
    public function createCancelConsignmentRequest(Shipment $shipment, string $authToken): CancelConsignmentRequest
    {
        $account = $shipment->getAccount();

        return new CancelConsignmentRequest(
            $account->getCredentials()['username'],
            $authToken,
            $shipment->getCourierReference()
        );
    }
}