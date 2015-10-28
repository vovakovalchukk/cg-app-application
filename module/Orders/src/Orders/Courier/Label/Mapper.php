<?php
namespace Orders\Courier\Label;

use CG\Dataplug\Request\CancelOrder as DataplugCancelOrderRequest;
use CG\Dataplug\Request\CreateOrders as DataplugCreateRequest;
use CG\Dataplug\Request\CreateOrders\Order as DataplugCreateRequestOrder;
use CG\Dataplug\Request\CreateOrders\Item as DataplugCreateRequestItem;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class Mapper
{
    public function ordersAndDataToDataplugCreateRequest(
        OrderCollection $orders,
        array $ordersData,
        array $orderParcelsData,
        OrganisationUnit $organisationUnit
    ) {
        $request = new DataplugCreateRequest();
        foreach ($orders as $order) {
            $orderData = $ordersData[$order->getId()];
            $parcelsData = $orderParcelsData[$order->getId()];
            $request->addOrder($this->orderAndDataToDataplugCreateRequestOrder($order, $orderData, $parcelsData, $organisationUnit));
        }
        return $request;
    }

    protected function orderAndDataToDataplugCreateRequestOrder(
        Order $order,
        array $orderData,
        array $parcelsData,
        OrganisationUnit $organisationUnit
    ) {
        $collectionDate = $this->sanitiseCollectionDate($orderData['collectionDate']);

        $dataplugOrder = new DataplugCreateRequestOrder();
        $dataplugOrder->setReference($order->getId())
            ->setCollectionDate($collectionDate)
            ->setProductCode($orderData['service'])
            ->setReadyAt(date('H:i'))
            ->setGoodsDescription($this->getGoodsDescriptionForOrder($order))
            ->setDeliveryInstructions($this->getOptionalParam('deliveryInstructions', $orderData))
            ->setAdultSignature($this->getOptionalParam('signature', $orderData))
            ->setPremiumInsurance($this->getOptionalParam('insurance', $orderData))
            ->setCollectionCompanyName(($organisationUnit->getAddressCompanyName() ?: $organisationUnit->getAddressFullName()))
            ->setCollectionAddressLine1($organisationUnit->getAddress1())
            ->setCollectionAddressLine2($organisationUnit->getAddress2())
            ->setCollectionCity($this->getCity($organisationUnit))
            ->setCollectionCounty($organisationUnit->getAddressCounty())
            ->setCollectionPostalCode($organisationUnit->getAddressPostcode())
            ->setCollectionCountryCode($organisationUnit->getAddressCountryCode())
            ->setCollectionContactName(($organisationUnit->getAddressFullName() ?: $organisationUnit->getAddressCompanyName()))
            ->setCollectionEmail($organisationUnit->getEmailAddress())
            ->setCollectionPhoneNumber($organisationUnit->getPhoneNumber())
            ->setDeliveryCompanyName(($order->getCalculatedShippingAddressCompanyName() ?: $order->getCalculatedShippingAddressFullName()))
            ->setDeliveryAddressLine1($order->getCalculatedShippingAddress1())
            ->setDeliveryAddressLine2($order->getCalculatedShippingAddress2())
            ->setDeliveryCity($this->getCity($order, 'CalculatedShipping'))
            ->setDeliveryPostalCode($order->getCalculatedShippingAddressPostcode())
            ->setDeliveryCountryCode($order->getCalculatedShippingAddressCountryCode())
            ->setDeliveryContactName(($order->getCalculatedShippingAddressFullName() ?: $order->getCalculatedShippingAddressCompanyName()))
            ->setDeliveryEmail($order->getCalculatedShippingEmailAddress())
            ->setDeliveryPhoneNumber($order->getCalculatedShippingPhoneNumber())
            ->setNumberOfPieces(count($parcelsData));

        $totalWeight = 0;
        $parcelValue = ($order->getTotal() - $order->getShippingPrice()) / count($parcelsData);
        foreach ($parcelsData as $parcelData) {
            if (!isset($parcelData['value'])) {
                $parcelData['value'] = $parcelValue;
            }
            $item = $this->orderParcelsDataToDataplugCreateRequestItem($order, $parcelData);
            $dataplugOrder->addItem($item);
            $totalWeight += $item->getWeight();
        }
        $dataplugOrder->setTotalWeight($totalWeight);

        return $dataplugOrder;
    }

    protected function orderParcelsDataToDataplugCreateRequestItem(Order $order, array $parcelData)
    {
        $item = new DataplugCreateRequestItem();
        $item->setHeight($this->getOptionalParam('height', $parcelData))
            ->setLength($this->getOptionalParam('length', $parcelData))
            ->setWeight($this->getOptionalParam('weight', $parcelData))
            ->setWidth($this->getOptionalParam('width', $parcelData))
            ->setValue($parcelData['value']);
        return $item;
    }

    protected function getOptionalParam($param, $params)
    {
        return (isset($params[$param]) && $params[$param] != '' ? $params[$param] : null);
    }

    protected function sanitiseCollectionDate($collectionDate)
    {
        if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $collectionDate)) {
            return $collectionDate;
        }
        return date('d/m/Y', strtotime($collectionDate));
    }

    protected function getGoodsDescriptionForOrder(Order $order)
    {
        $descriptions = [];
        foreach ($order->getItems() as $orderItem) {
            $itemDesc = (trim($orderItem->getItemSku()) ?: $orderItem->getItemName());
            $descriptions[] = $orderItem->getItemQuantity() . ' x ' . $itemDesc;
        }
        return implode('/ ', $descriptions);
    }

    protected function getCity($address, $prefix = '')
    {
        $cityGetter = 'get' . ucfirst($prefix) . 'AddressCity';
        if ($address->$cityGetter()) {
            return $address->$cityGetter();
        }
        $line3Getter = 'get' . ucfirst($prefix) . 'Address3';
        if ($address->$line3Getter()) {
            return $address->$line3Getter();
        }
        $line2Getter = 'get' . ucfirst($prefix) . 'Address2';
        if ($address->$line2Getter()) {
            return $address->$line2Getter();
        }
        return '';
    }

    public function orderLabelToDataplugCancelRequest(OrderLabel $orderLabel)
    {
        $request = new DataplugCancelOrderRequest();
        $request->setOrderNumber($orderLabel->getExternalId());
        return $request;
    }
}
