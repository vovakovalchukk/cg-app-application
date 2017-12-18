<?php
namespace CG\ShipStation\Carrier\Label;

use CG\Account\Shared\Entity as Account;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Client as ShipStationClient;
use CG\ShipStation\Request\Shipping\Label as LabelRequest;
use CG\ShipStation\Request\Shipping\Shipments as ShipmentsRequest;
use CG\ShipStation\Response\Shipping\Label as LabelResponse;
use CG\ShipStation\Response\Shipping\Shipments as ShipmentsResponse;
use CG\User\Entity as User;
use Guzzle\Http\Client as GuzzleClient;

class Creator
{
    const LABEL_FORMAT = 'pdf';

    /** @var ShipStationClient */
    protected $shipStationClient;
    /** @var GuzzleClient */
    protected $guzzleClient;

    public function __construct(ShipStationClient $shipStationClient, GuzzleClient $guzzleClient)
    {
        $this->shipStationClient = $shipStationClient;
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @param OrderCollection $orders The orders to operate on
     * @param OrderLabelCollection $orderLabels Pre-created OrderLabels to save label PDF data to
     * @param array $ordersData Additional data for each Order:
     *         ['{orderId}' => ['signature' => bool, 'deliveryInstructions' => string, ...]]
     * @param array $orderParcelsData Additional data for each parcel:
     *         ['{orderId}' => ['{parcelIndex}' => ['value' => float, 'height' => float, ...]]]
     * @param array $orderItemsData Additional data for each item:
     *         ['{orderId}' => ['{itemId}' => ['weight' => float, 'hstariff' => string, ...]]]
     * @param OrganisationUnit $rootOu
     * @param Account $shippingAccount
     * @param User $user The user who triggered the request. Required if creating Order\Trackings
     * @return array ['{orderId}' => bool || CG\Stdlib\Exception\Runtime\ValidationMessagesException]
     *          for each order whether a label was successfully created or a ValidationMessagesException if it errored
     */
    public function createLabelsForOrders(
        OrderCollection $orders,
        OrderLabelCollection $orderLabels,
        array $ordersData,
        array $orderParcelsData,
        array $orderItemsData,
        OrganisationUnit $rootOu,
        Account $shippingAccount,
        User $user,
        Account $shipStationAccount
    ) {
        $shipments = $this->createShipmentsForOrders($orders, $ordersData, $orderParcelsData, $shipStationAccount);
        $labels = $this->createLabelsForShipments($shipments, $shipStationAccount);
        $labelPdfs = $this->downloadPdfsForLabels($labels);
    }

    protected function createShipmentsForOrders(
        OrderCollection $orders,
        array $ordersData,
        array $orderParcelsData,
        Account $shipStationAccount
    ): ShipmentsResponse {
        $request = ShipmentsRequest::createFromOrdersAndData($orders, $ordersData, $orderParcelsData, $shipStationAccount);
        return $this->shipStationClient->sendRequest($request, $shipStationAccount);
    }

    protected function createLabelsForShipments(ShipmentsResponse $shipments, Account $shipStationAccount): array
    {
        $labels = [];
        foreach ($shipments as $shipment) {
            $request = new LabelRequest($shipment->getShipmentId(), static::LABEL_FORMAT, $this->isTestLabel());
            $labels[] = $this->shipStationClient->sendRequest($request, $shipStationAccount);
        }
        return $labels;
    }

    protected function isTestLabel(): bool
    {
        return (ENVIRONMENT != 'live');
    }

    protected function downloadPdfsForLabels(array $labels)
    {
        $requests = [];
        /** @var LabelResponse $label */
        foreach ($labels as $label) {
            $requests[] = $this->guzzleClient->get($label->getLabelDownload()->getHref());
        }
        $responses = $this->guzzleClient->send($requests);
        // TODO: get pdf data from responses
    }
}