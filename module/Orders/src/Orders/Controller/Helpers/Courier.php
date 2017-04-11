<?php
namespace Orders\Controller\Helpers;

use CG\Channel\Carrier;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Stdlib\Exception\Runtime\NotFound;
use Orders\Courier\Manifest\Service as ManifestService;
use Orders\Courier\Service as CourierService;

class Courier
{
    /** @var CourierService */
    protected $courierService;
    /** @var ManifestService */
    protected $manifestService;
    /** @var OrderLabelService */
    protected $orderLabelService;
    /** @var Carrier $carriers */
    protected $carriers;

    public function __construct(
        CourierService $courierService,
        ManifestService $manifestService,
        OrderLabelService $orderLabelService,
        Carrier $carriers
    ) {
        $this->courierService = $courierService;
        $this->manifestService = $manifestService;
        $this->orderLabelService = $orderLabelService;
        $this->carriers = $carriers;
    }

    public function hasCourierAccounts()
    {
        try {
            $courierAccounts = $this->courierService->getShippingAccounts();
            return (count($courierAccounts) > 0);
        } catch (NotFound $e) {
            return false;
        }
    }

    public function hasManifestableCourierAccounts()
    {
        try {
            $manifestableAccounts = $this->manifestService->getShippingAccounts();
            return (count($manifestableAccounts) > 0);
        } catch (NotFound $e) {
            return false;
        }
    }

    public function getNonCancelledOrderLabelsForOrders(array $orderIds)
    {
        $labelStatuses = OrderLabelStatus::getAllStatuses();
        $labelStatusesNotCancelled = array_diff($labelStatuses, [OrderLabelStatus::CANCELLED]);
        $filter = (new OrderLabelFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderId($orderIds)
            ->setStatus($labelStatusesNotCancelled);
        return $this->orderLabelService->fetchCollectionByFilter($filter);
    }

    public function getPrintableOrderLabelForOrder(Order $order)
    {
        $labelStatuses = OrderLabelStatus::getPrintableStatuses();
        $filter = (new OrderLabelFilter())
            ->setLimit(1)
            ->setPage(1)
            ->setOrderId([$order->getId()])
            ->setStatus($labelStatuses);
        $orderLabels = $this->orderLabelService->fetchCollectionByFilter($filter);
        $orderLabels->rewind();
        return $orderLabels->current();
    }

    public function getCarriersData()
    {
        return $this->carriers->getAllCarriers();
    }

    public function getCarrierPriorityOptions()
    {
        $frequentCarrierList = [
            'DPD',
            'DPD Local',
            'MyHermes',
            'Royal Mail',
        ];

        $carrierDropdownOptions = [];
        foreach ($frequentCarrierList as $carrier) {
            $carrierDropdownOptions[] = [
                'title' => $carrier,
                'value' => $carrier,
            ];
        }
        return $carrierDropdownOptions;
    }
}
