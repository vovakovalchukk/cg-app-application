<?php
namespace Orders\Courier\Label;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\Service\Repository as CarrierProviderServiceRepository;
use CG\Billing\Shipping\Charge\Collection as ShippingChargeCollection;
use CG\Billing\Shipping\Charge\Entity as ShippingCharge;
use CG\Billing\Shipping\Charge\DefaultCharge as DefaultShippingCharge;
use CG\Billing\Shipping\Charge\Filter as ShippingChargeFilter;
use CG\Billing\Shipping\Charge\Service as ShippingChargeService;
use CG\Channel\Shipping\Provider\Service\FetchRatesInterface;
use CG\Channel\Shipping\Provider\Service\ShippingRateInterface;
use CG\Channel\Shipping\Provider\Service\ShippingRate\OrderRates as OrderShippingRates;
use CG\Channel\Shipping\Provider\Service\ShippingRate\OrderRates\Collection as ShippingRateCollection;
use CG\Locale\Mass as LocaleMass;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\OrganisationUnit\Service as UserOuService;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

class RatesService implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'OrderCourierLabelRatesService';

    /** @var AccountService */
    protected $accountService;
    /** @var UserOuService */
    protected $userOuService;
    /** @var OrderService */
    protected $orderService;
    /** @var CarrierProviderServiceRepository */
    protected $carrierProviderServiceRepo;
    /** @var ShippingChargeService */
    protected $shippingChargeService;

    public function __construct(
        AccountService $accountService,
        UserOuService $userOuService,
        OrderService $orderService,
        CarrierProviderServiceRepository $carrierProviderServiceRepo,
        ShippingChargeService $shippingChargeService
    ) {
        $this->accountService = $accountService;
        $this->userOuService = $userOuService;
        $this->orderService = $orderService;
        $this->carrierProviderServiceRepo = $carrierProviderServiceRepo;
        $this->shippingChargeService = $shippingChargeService;
    }

    public function fetchRates(
        array $orderIds,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $ordersParcelsData,
        OrderItemsDataCollection $ordersItemsData,
        int $shippingAccountId
    ): ShippingRateCollection {
        $rootOu = $this->userOuService->getRootOuByActiveUser();
        $this->addGlobalLogEventParams(['ou' => $rootOu->getId(), 'rootOu' => $rootOu->getId(), 'account' => $shippingAccountId]);

        try {
            $shippingAccount = $this->accountService->fetch($shippingAccountId);
            $orders = $this->getOrdersByIds($orderIds);
            $shippingRates = $this->fetchRatesFromProvider(
                $orders,
                $ordersData,
                $ordersParcelsData,
                $ordersItemsData,
                $shippingAccount,
                $rootOu
            );
            return $this->addShippingChargeToRates($shippingRates, $shippingAccount, $ordersParcelsData, $rootOu);

        } finally {
            $this->removeGlobalLogEventParams(['ou', 'account']);
        }
    }

    protected function getOrdersByIds(array $orderIds)
    {
        $filter = (new OrderFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderIds($orderIds);
        return $this->orderService->fetchLinkedCollectionByFilter($filter);
    }

    protected function fetchRatesFromProvider(
        OrderCollection $orders,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $ordersParcelsData,
        OrderItemsDataCollection $ordersItemsData,
        Account $shippingAccount,
        OrganisationUnit $rootOu
    ): ShippingRateCollection {
        /** @var FetchRatesInterface $carrier */
        $carrier = $this->carrierProviderServiceRepo->getProviderForAccount($shippingAccount);

        $this->logDebug('Fetching shipping rates for orders %s with shipping account %d', [implode(',', $orders->getIds()), $shippingAccount->getId()], [static::LOG_CODE, 'FetchRates']);
        return $carrier->fetchRatesForOrders(
            $orders,
            $ordersData,
            $ordersParcelsData,
            $ordersItemsData,
            $rootOu,
            $shippingAccount
        );
    }

    protected function addShippingChargeToRates(
        ShippingRateCollection $shippingRates,
        Account $shippingAccount,
        OrderParcelsDataCollection $ordersParcelsData,
        OrganisationUnit $rootOu
    ): ShippingRateCollection {
        $shippingCharges = $this->fetchShippingCharges($shippingAccount);
        /** @var OrderShippingRates $orderRates */
        foreach ($shippingRates as $orderRates) {
            $this->addGlobalLogEventParam('order', $orderRates->getOrderId());
            /** @var OrderParcelsData $orderParcelsData */
            $orderParcelsData = $ordersParcelsData->getById($orderRates->getOrderId());
            /** @var ShippingRateInterface $shippingRate */
            foreach ($orderRates as $shippingRate) {
                $this->addShippingChargeToRate($shippingRate, $orderParcelsData, $rootOu, $shippingCharges);
            }
            $this->removeGlobalLogEventParam('order');
        }
        return $shippingRates;
    }

    protected function addShippingChargeToRate(
        ShippingRateInterface $shippingRate,
        OrderParcelsData $orderParcelsData,
        OrganisationUnit $rootOu,
        ShippingChargeCollection $shippingCharges
    ): void {
        $this->addGlobalLogEventParams(['shippingRate' => $shippingRate->getId(), 'service' => $shippingRate->getServiceCode()]);
        $chargesForService = $this->getShippingChargesForService($shippingRate->getServiceCode(), $shippingCharges);
        $weight = $this->convertWeightForShippingChargeComparison($orderParcelsData->getTotalWeight(), $rootOu);
        $this->logDebug('Adding applicable charges to rate %s, service %s for weight %.4f(kg)', [$shippingRate->getId(), $shippingRate->getServiceCode(), $weight], [static::LOG_CODE, 'AddChargesToRate']);

        /** @var ShippingCharge $shippingCharge */
        foreach ($chargesForService as $shippingCharge) {
            if (!$shippingCharge->isApplicableForWeight($weight)) {
                continue;
            }
            $originalCost = $shippingRate->getCost();
            $amendedCost = $shippingCharge->apply($originalCost);
            $shippingRate->setCost($amendedCost);
            $this->logDebug('ShippingCharge %s (%s) was applied to rate %s, cost was %.2f now %.2f', ['shippingCharge' => $shippingCharge->getId(), $shippingCharge->getAmountString(), $shippingRate->getId(), $originalCost, $amendedCost], [static::LOG_CODE, 'ChargeApplied']);
        }
        $this->removeGlobalLogEventParams(['shippingRate', 'service']);
    }

    protected function fetchShippingCharges(Account $shippingAccount): ShippingChargeCollection
    {
        try {
            $this->logDebug('Fetching shipping charges for shipping account %d', [$shippingAccount->getId()], [static::LOG_CODE, 'FetchCharges']);
            $filter = $this->buildShippingChargeFilterFromAccount($shippingAccount);
            return $this->shippingChargeService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return new ShippingChargeCollection(ShippingCharge::class, __FUNCTION__);
        }

    }

    protected function buildShippingChargeFilterFromAccount(Account $shippingAccount): ShippingChargeFilter
    {
        return (new ShippingChargeFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setChannel([$shippingAccount->getChannel()]);
    }

    protected function getShippingChargesForService(string $serviceCode, ShippingChargeCollection $shippingCharges): ShippingChargeCollection
    {
        $chargesForService = $shippingCharges->getBy('serviceCode', $serviceCode);
        if ($chargesForService->count() > 0) {
            return $chargesForService;
        }
        $this->logDebug('No applicable shipping charges for service %s, will use default', [$serviceCode], [static::LOG_CODE, 'DefaultCharges']);
        return $this->getDefaultShippingCharges();
    }

    protected function getDefaultShippingCharges(): ShippingChargeCollection
    {
        $shippingCharge = new DefaultShippingCharge();
        $chargesForService = new ShippingChargeCollection(ShippingCharge::class, __FUNCTION__);
        $chargesForService->attach($shippingCharge);
        return $chargesForService;
    }

    protected function convertWeightForShippingChargeComparison(float $weight, OrganisationUnit $rootOu): float
    {
        $inputWeightUnits = LocaleMass::getForLocale($rootOu->getLocale());
        $shippingChargeWeightUnits = ShippingCharge::WEIGHT_UNIT;
        return (new Mass($weight, $inputWeightUnits))->toUnit($shippingChargeWeightUnits);
    }
}