<?php
namespace CG\ShipStation\Carrier\Label;

use CG\Account\Shared\Entity as Account;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\ShipStation\Client as ShipStationClient;
use CG\ShipStation\Request\Shipping\VoidLabel as VoidLabelRequest;
use CG\ShipStation\Response\Shipping\VoidLabel as VoidLabelResponse;
use CG\Stdlib\Exception\Storage as StorageException;

class Canceller
{
    /** @var ShipStationClient */
    protected $shipStationClient;

    public function __construct(ShipStationClient $shipStationClient)
    {
        $this->shipStationClient = $shipStationClient;
    }

    public function cancelOrderLabels(
        OrderLabelCollection $orderLabels,
        OrderCollection $orders,
        Account $shippingAccount,
        Account $shipStationAccount
    ) {
        $exceptions = [];

        /** @var OrderLabel $orderLabel */
        foreach ($orderLabels as $orderLabel) {
            /** @var VoidLabelResponse $response */
            $response = $this->shipStationClient->sendRequest(
                new VoidLabelRequest($orderLabel->getExternalId()),
                $shipStationAccount
            );

            if (!$response->isApproved()) {
                $exceptions[] = sprintf('  %s: %s', $orderLabel->getOrderId(), $response->getMessage());
            }
        }

        if (empty($exceptions)) {
            return;
        }

        throw new StorageException('Failed to cancel all order labels' . PHP_EOL . implode(PHP_EOL, $exceptions));
    }
}