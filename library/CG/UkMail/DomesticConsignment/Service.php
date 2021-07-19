<?php
namespace CG\UkMail\DomesticConsignment;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UkMail\Request\Rest\DomesticConsignment;
use CG\UkMail\Shipment;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    public function __construct()
    {

    }
    
    protected function createDomesticConsignmentRequest(
        CourierAdapterAccount $account,
        Shipment $shipment,
        string $authToken,
        string $collectionJobNumber

    ) {
        return new DomesticConsignment(
            $account->getCredentials()['apiKey'],
            $account->getCredentials()['username'],
            $authToken,
            $account->getCredentials()['accountNumber'],
            $collectionJobNumber,
            $deliveryDetails,
            $serviceKey,
            $items,
            $totalWeight,
            $customerReference,
            $alternativeReference,
            $parcels,
            $extendedCoverUnits,
            $recipient,
            $exchangeOnDelivery,
            $bookin,
            $inBoxReturn,
            $inBoxReturnDetail,
            $labelFormat
        );
    }
}