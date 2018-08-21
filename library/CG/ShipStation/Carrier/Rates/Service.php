<?php
namespace CG\ShipStation\Carrier\Rates;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\Service\ShippingRate\OrderRates\Collection as ShippingRateCollection;
use CG\Channel\Shipping\Provider\Service\ShippingRate\OrderRates as OrderShippingRates;
use CG\Http\StatusCode;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Client;
use CG\ShipStation\Messages\Rate as ShipStationRate;
use CG\ShipStation\Messages\Shipment;
use CG\ShipStation\Request\Shipping\Rates as RatesRequest;
use CG\ShipStation\Response\Shipping\Rates as RatesResponse;
use CG\ShipStation\ShipStation\Service as ShipStationService;
use CG\Stdlib\Exception\Runtime\ValidationException;
use CG\Stdlib\Exception\Runtime\ValidationMessagesException;

class Service
{
    const DEFAULT_RATE_ERROR = 'No rates found. Please check the data you entered and try again.';

    /** @var ShipStationService */
    protected $shipStationService;
    /** @var Client */
    protected $client;

    public function __construct(
        ShipStationService $shipStationService,
        Client $client
    ) {
        $this->shipStationService = $shipStationService;
        $this->client = $client;
    }

    public function fetchRatesForOrders(
        OrderCollection $orders,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $ordersParcelsData,
        OrderItemsDataCollection $ordersItemsData,
        OrganisationUnit $rootOu,
        Account $shippingAccount,
        Account $shipStationAccount
    ): ShippingRateCollection {

        $rates = new ShippingRateCollection();
        $exception = new ValidationMessagesException(StatusCode::BAD_REQUEST);
        foreach ($orders as $order) {
            try {
                $orderData = $ordersData->getById($order->getId());
                $shipStationRates = $this->fetchRatesForOrderFromShipStation(
                    $order,
                    $orderData,
                    $ordersParcelsData->getById($order->getId()),
                    $shipStationAccount,
                    $shippingAccount,
                    $rootOu
                );
                $shipStationRates = $this->filterShipStationRatesByPackageType($shipStationRates, $orderData->getPackageType());
                $orderRates = $this->mapShipstationRatesToOrderShippingRates($order->getId(), $shipStationRates);
                $rates->attach($orderRates);
            } catch (ValidationException $e) {
                $exception->addErrorWithField($order->getExternalId(), $e->getMessage());
            }
        }
        if (!empty($exception->getErrors())) {
            throw $exception;
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
        try {
            /** @var RatesResponse $response */
            $response = $this->client->sendRequest($request, $shipStationAccount);
        } catch (\Exception $e) {
            throw new ValidationException(static::DEFAULT_RATE_ERROR, $e->getCode(), $e);
        }
        if (!empty($response->getRates())) {
            return $response->getRates();
        }
        return $this->handleInvalidRatesResponse($response, $order);
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

    protected function handleInvalidRatesResponse(RatesResponse $response, Order $order): void
    {
        if (empty($response->getInvalidRates())) {
            // No rates and no invalid rates, nothing to go off, show generic error
            throw new ValidationException(static::DEFAULT_RATE_ERROR);
        }
        /** @var ShipStationRate $invalidRate */
        foreach ($response->getInvalidRates() as $invalidRate) {
            if (empty($invalidRate->getErrorMessages())) {
                continue;
            }
            // Each potential rate is returned with a reason why it can't be used. The error is usually the same for each
            // e.g. the weight is too high. Rather than show them all to the user we just show the first set of errors.
            throw new ValidationException(implode('; ', $invalidRate->getErrorMessages()));
        }
        // No exception thrown yet, throw a generic one
        throw new ValidationException(static::DEFAULT_RATE_ERROR);
    }

    protected function filterShipStationRatesByPackageType(array $shipStationRates, string $packageType): array
    {
        foreach ($shipStationRates as $key => $shipStationRate) {
            if ($shipStationRate->getPackageType() !== $packageType) {
                 unset($shipStationRates[$key]);
            }
        }
        if (count($shipStationRate === 0)) {
            throw new ValidationException('No rates found for the selected package type. Please select another package type and try again.');
        }
        return $shipStationRates;
    }
}