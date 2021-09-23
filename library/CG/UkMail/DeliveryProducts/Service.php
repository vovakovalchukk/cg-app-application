<?php
namespace CG\UkMail\DeliveryProducts;

use CG\CourierAdapter\Exception\UserError;
use CG\UkMail\Client\Factory as ClientFactory;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UkMail\Shipment;
use CG\UkMail\Response\Rest\DeliveryProducts as DeliveryProductsResponse;
use CG\UkMail\Request\Rest\DeliveryProducts as DeliveryProductsRequest;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected const INTL_ROAD_ECONOMY_SERVICE = 204;

    protected const LOG_CODE = 'UkMailDeliveryProductsService';
    protected const LOG_REQUESTING_LABEL_MSG = 'Requesting UK Mail label for account %d order %s';

    /** @var ClientFactory */
    protected $clientFactory;
    /** @var Mapper */
    protected $mapper;

    public function __construct(ClientFactory $clientFactory, Mapper $mapper)
    {
        $this->clientFactory = $clientFactory;
        $this->mapper = $mapper;
    }

    public function checkIntlServiceAvailabilityForShipment(Shipment $shipment): ?DeliveryProduct
    {
        $deliveryProducts = $this->getDeliveryProducts($shipment);

        /** @var DeliveryProduct $deliveryProduct */
        foreach ($deliveryProducts->getDeliveryProducts() as $deliveryProduct) {
            if ($deliveryProduct->getProductCode() == $shipment->getDeliveryService()->getReference()) {
                return $deliveryProduct;
            }
        }
        
        if ($shipment->getDeliveryService()->getReference() == static::INTL_ROAD_ECONOMY_SERVICE) {
            return $this->getDeliveryProductsService204();
        }

        return null;
    }

    public function getDeliveryProducts(Shipment $shipment): DeliveryProductsResponse
    {
        $this->logDebug(static::LOG_REQUESTING_LABEL_MSG, [$shipment->getAccount()->getId(), $shipment->getCustomerReference()], static::LOG_CODE);
        $deliveryProductsRequest = $this->createDeliveryProductsRequest($shipment);
        try {
            $client = ($this->clientFactory)($shipment->getAccount(), $deliveryProductsRequest);
            return $client->sendRequest($deliveryProductsRequest);
        } catch (\Exception $exception) {
            throw new UserError($exception->getMessage());
        }
    }

    protected function createDeliveryProductsRequest(Shipment $shipment):DeliveryProductsRequest
    {
        return $this->mapper->createDeliveryProductsRequest($shipment);
    }

    /**
     * UkMail is not returning service 204 in DeliveryProductRequest as call is not yet optimised
     * @return DeliveryProduct
     */
    protected function getDeliveryProductsService204(): DeliveryProduct
    {
        return $this->mapper->getDeliveryProductsService204();
    }
}