<?php
namespace CG\ShipStation\Carrier\Rates;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\Service\ShippingRate\OrderRates\Collection as ShippingRateCollection;
use CG\Channel\Shipping\Provider\Service\ShippingRate\OrderRates as OrderShippingRates;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Carrier\AccountDeciderInterface;
use CG\ShipStation\Carrier\AccountDecider\Factory as AccountDeciderFactory;
use CG\ShipStation\Client;
use CG\ShipStation\Messages\Rate as ShipStationRate;
use CG\ShipStation\Messages\Shipment;
use CG\ShipStation\Request\Shipping\Rates as RatesRequest;
use CG\ShipStation\Response\Shipping\Rates as RatesResponse;
use CG\ShipStation\ShipStation\Service as ShipStationService;

class Service
{
    /** @var ShipStationService */
    protected $shipStationService;
    /** @var AccountDeciderFactory */
    protected $accountDeciderFactory;
    /** @var Client */
    protected $client;

    public function __construct(
        ShipStationService $shipStationService,
        AccountDeciderFactory $accountDeciderFactory,
        Client $client
    ) {
        $this->shipStationService = $shipStationService;
        $this->accountDeciderFactory = $accountDeciderFactory;
        $this->client = $client;
    }

    public function fetchRatesForOrders(
        OrderCollection $orders,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $ordersParcelsData,
        OrderItemsDataCollection $ordersItemsData,
        OrganisationUnit $rootOu,
        Account $shippingAccount
    ): ShippingRateCollection {
        /** @var AccountDeciderInterface $accountDecider */
        $accountDecider = ($this->accountDeciderFactory)($shippingAccount->getChannel());
        $shipStationAccountToUse = $accountDecider->getShipStationAccountForRequests($shippingAccount);
        $shippingAccountToUse = $accountDecider->getShippingAccountForRequests($shippingAccount);

        $rates = new ShippingRateCollection();
        foreach ($orders as $order) {
            $shipStationRates = $this->fetchRatesForOrderFromShipStation(
                $order,
                $ordersData->getById($order->getId()),
                $ordersParcelsData->getById($order->getId()),
                $shipStationAccountToUse,
                $shippingAccountToUse,
                $rootOu
            );
            $orderRates = $this->mapShipstationRatesToOrderShippingRates($order->getId(), $shipStationRates);
            $rates->attach($orderRates);
        }
        return $rates;
    }

    protected function fetchRatesForOrderFromShipStation(
        Order $order,
        OrderData $orderData,
        OrderParcelsData $parcelsData,
        Account $shipStationAccount,
        Account $shippingAccount,
        OrganisationUnit $rootOu
    ): array {
        $shipment = Shipment::createFromOrderAndData(
            $order,
            $orderData,
            $parcelsData,
            $shipStationAccount,
            $shippingAccount,
            $rootOu
        );
        $request = new RatesRequest($shipment, [$shippingAccount->getExternalId()]);
        /** @var RatesResponse $response */
        $response = $this->client->sendRequest($request, $shipStationAccount);
        return $response->getRates();
    }

    protected function mapShipstationRatesToOrderShippingRates(string $orderId, array $shipStationRates): OrderShippingRates
    {
        $orderRates = new OrderShippingRates($orderId);
        /** @var ShipStationRate $shipStationRate */
        foreach ($shipStationRates as $shipStationRate) {
            $shippingRate = Entity::fromShipEngineRate($shipStationRate);
            $orderRates->attach($shippingRate);
        }
        return $orderRates;
    }
}