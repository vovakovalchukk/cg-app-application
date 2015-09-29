<?php
namespace Orders\Courier\Label;

use CG\Dataplug\Request\CancelOrder as DataplugCancelOrderRequest;
use CG\Dataplug\Request\CreateOrders as DataplugCreateRequest;
use CG\Dataplug\Request\CreateOrders\Order as DataplugCreateRequestOrder;
use CG\Dataplug\Request\CreateOrders\Item as DataplugCreateRequestItem;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class Mapper
{
    public function orderAndDataToDataplugCreateRequest(
        Order $order,
        array $orderData,
        array $parcelsData,
        OrganisationUnit $organisationUnit
    ) {
        $request = new DataplugCreateRequest();
        $request->addOrder($this->orderAndDataToDataplugCreateRequestOrder($order, $orderData, $parcelsData, $organisationUnit));
        return $request;
    }

    protected function orderAndDataToDataplugCreateRequestOrder(
        Order $order,
        array $orderData,
        array $parcelsData,
        OrganisationUnit $organisationUnit
    ) {
        $deliveryAddress = $order->getShippingAddress();
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
            ->setCollectionCompanyName($this->getCompanyName($organisationUnit))
            ->setCollectionAddressLine1($organisationUnit->getAddress1())
            ->setCollectionAddressLine2($organisationUnit->getAddress2())
            ->setCollectionCity($this->getCity($organisationUnit))
            ->setCollectionCounty($organisationUnit->getAddressCounty())
            ->setCollectionPostalCode($organisationUnit->getAddressPostcode())
            ->setCollectionCountryCode($organisationUnit->getAddressCountryCode())
            ->setCollectionContactName($this->getContactName($organisationUnit))
            ->setCollectionEmail($organisationUnit->getEmailAddress())
            ->setCollectionPhoneNumber($organisationUnit->getPhoneNumber())
            ->setDeliveryCompanyName($this->getCompanyName($deliveryAddress))
            ->setDeliveryAddressLine1($deliveryAddress->getAddress1())
            ->setDeliveryAddressLine2($deliveryAddress->getAddress2())
            ->setDeliveryCity($this->getCity($deliveryAddress))
            ->setDeliveryPostalCode($deliveryAddress->getAddressPostcode())
            ->setDeliveryCountryCode($deliveryAddress->getAddressCountryCode())
            ->setDeliveryContactName($this->getContactName($deliveryAddress))
            ->setDeliveryEmail($deliveryAddress->getEmailAddress())
            ->setDeliveryPhoneNumber($deliveryAddress->getPhoneNumber())
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
            $descriptions[] = $orderItem->getItemSku() . ': ' . $orderItem->getItemName() . ' x ' . $orderItem->getItemQuantity();
        }
        return preg_replace('/[^a-zA-Z0-9 \-\.:;\/\[\]\\\\]/', '', implode('; ', $descriptions));
    }

    protected function getCompanyName($address)
    {
        return ($address->getAddressCompanyName() ?: $address->getAddressFullName());
    }

    protected function getCity($address)
    {
        if ($address->getAddressCity()) {
            return $address->getAddressCity();
        }
        if ($address->getAddress3()) {
            return $address->getAddress3();
        }
        if ($address->getAddress2()) {
            return $address->getAddress2();
        }
        return '';
    }

    protected function getContactName($address)
    {
        return ($address->getAddressFullName() ?: $address->getAddressCompanyName());
    }

    public function orderLabelToDataplugCancelRequest(OrderLabel $orderLabel)
    {
        $request = new DataplugCancelOrderRequest();
        $request->setOrderNumber($orderLabel->getExternalId());
        return $request;
    }
}
