<?php
namespace Orders\ManualOrder;

use CG\Account\Shared\Entity as Account;
use CG\Currency\Formatter as CurrencyFormatter;
use CG\Http\SaveCollectionHandleErrorsTrait;
use CG\Locale\CountryNameByCode;
use CG\ManualOrder\Account\Service as ManualOrderAccountService;
use CG\Order\Client\Item\Service as OrderItemService;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Alert\Service as OrderAlertService;
use CG\Order\Service\Note\Service as OrderNoteService;
use CG\Order\Shared\Alert\Mapper as OrderAlertMapper;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Collection as OrderItemCollection;
use CG\Order\Shared\Item\Entity as OrderItem;
use CG\Order\Shared\Item\Mapper as OrderItemMapper;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Order\Shared\Note\Mapper as OrderNoteMapper;
use CG\Order\Shared\Shipping\Conversion\Service as ConversionService;
use CG\Order\Shared\Status as OrderStatus;
use CG\Order\Shared\Tax\Service as TaxService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\Client\Service as ProductService;
use CG\Product\Entity as Product;
use CG\Product\Filter as ProductFilter;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\User\OrganisationUnit\Service as UserOuService;
use Orders\Order\CurrencyService;

class Service implements LoggerAwareInterface
{
    use LogTrait;
    use SaveCollectionHandleErrorsTrait;

    /** @var ManualOrderAccountService */
    protected $manualOrderAccountService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var ProductService */
    protected $productService;
    /** @var OrderService */
    protected $orderService;
    /** @var OrderItemService */
    protected $orderItemService;
    /** @var OrderAlertService */
    protected $orderAlertService;
    /** @var OrderNoteService */
    protected $orderNoteService;
    /** @var OrderMapper */
    protected $orderMapper;
    /** @var OrderItemMapper */
    protected $orderItemMapper;
    /** @var OrderAlertMapper */
    protected $orderAlertMapper;
    /** @var OrderNoteMapper */
    protected $orderNoteMapper;
    /** @var TaxService */
    protected $taxService;
    /** @var CurrencyService */
    protected $currencyService;
    /** @var UserOuService */
    protected $userOuService;
    /** @var  ConversionService */
    protected $conversionService;

    public function __construct(
        ManualOrderAccountService $manualOrderAccountService,
        ActiveUserInterface $activeUserContainer,
        OrganisationUnitService $organisationUnitService,
        ProductService $productService,
        OrderService $orderService,
        OrderItemService $orderItemService,
        OrderAlertService $orderAlertService,
        OrderNoteService $orderNoteService,
        OrderMapper $orderMapper,
        OrderItemMapper $orderItemMapper,
        OrderAlertMapper $orderAlertMapper,
        OrderNoteMapper $orderNoteMapper,
        TaxService $taxService,
        CurrencyService $currencyService,
        UserOuService $userOuService,
        ConversionService $conversionService
    ) {
        $this->setManualOrderAccountService($manualOrderAccountService)
            ->setActiveUserContainer($activeUserContainer)
            ->setOrganisationUnitService($organisationUnitService)
            ->setProductService($productService)
            ->setOrderService($orderService)
            ->setOrderItemService($orderItemService)
            ->setOrderAlertService($orderAlertService)
            ->setOrderNoteService($orderNoteService)
            ->setOrderMapper($orderMapper)
            ->setOrderItemMapper($orderItemMapper)
            ->setOrderAlertMapper($orderAlertMapper)
            ->setOrderNoteMapper($orderNoteMapper)
            ->setTaxService($taxService)
            ->setCurrencyService($currencyService)
            ->setConversionService($conversionService)
            ->setUserOuService($userOuService);
    }

    /**
     * @return Order
     */
    public function createOrderFromPostData(array $orderData)
    {
        if (empty($orderData['item'])) {
            throw new \BadFunctionCallException("Please add at least one product to the order");
        }
        $organisationUnitId = $this->getOrganisationUnitIdForOrderCreation($orderData);
        $organisationUnit = $this->organisationUnitService->fetch($organisationUnitId);
        $account = $this->manualOrderAccountService->getAccountForOrganisationUnit($organisationUnit);

        $order = $this->createOrder($orderData, $account);
        $this->createItems($orderData['item'], $order)
            ->createAlert($orderData['alert'], $order);
        if (isset($orderData['note']) && !empty($orderData['note'])) {
            $this->createNotes($orderData['note'], $order);
        }

        return $order;
    }
    
    protected function getOrganisationUnitIdForOrderCreation(array $orderData)
    {
        if (isset($orderData['organisationUnitId']) && is_numeric($orderData['organisationUnitId'])) {
            return $orderData['organisationUnitId'];
        }
        return $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
    }

    protected function createOrder(array $orderData, Account $account)
    {
        $orderId = $this->manualOrderAccountService->getNextOrderIdForAccount($account);
        $orderData['accountId'] = $account->getId();
        $orderData['externalId'] = $orderId;
        $orderData['id'] = $account->getId() . '-' . $orderId;
        $orderData['channel'] = $account->getChannel();
        $orderData['organisationUnitId'] = $account->getOrganisationUnitId();
        $orderData['status'] = OrderStatus::AWAITING_PAYMENT;
        $orderData['purchaseDate'] = (new StdlibDateTime())->stdFormat();
        $orderData['paymentDate'] = null;
        $orderData['printedDate'] = null;
        $orderData['dispatchDate'] = null;
        $orderData['total'] = $this->calculateTotalFromOrderData($orderData);

        if (isset($orderData['shippingAddressSameAsBilling']) && 
            filter_var($orderData['shippingAddressSameAsBilling'], FILTER_VALIDATE_BOOLEAN) == true
        ) {
            $this->copyBillingAddressToShippingAddress($orderData);
        }
        $this->ensureCountryCodes($orderData);

        unset($orderData['item'], $orderData['alert'], $orderData['note']);
        $order = $this->orderMapper->fromArray($orderData);
        return $this->orderService->save($order);
    }

    public function getCurrencyOptions(?Order $order = null)
    {
        $currencyCodes = array_keys(array_merge($this->currencyService->getPriorityActiveUserCurrencies(), $this->currencyService->getActiveUserCurrencies()));
        $formatter = new CurrencyFormatter($this->userOuService->getRootOuByActiveUser());

        $currencyOptions = [];
        foreach ($currencyCodes as $code) {
            $currencyOptions[] = [
                'name' => $code,
                'value' => $formatter->getSymbol($code),
                'selected' => $order ? $order->getCurrencyCode() == $code : false
            ];
        }
        return $currencyOptions;
    }

    public function getShippingMethods()
    {
        $organisationUnit = $this->userOuService->getRootOuByActiveUser();
        return $this->conversionService->fetchMethods($organisationUnit, 'manual-order');
    }

    protected function calculateTotalFromOrderData(array $orderData)
    {
        $total = 0;
        foreach ($orderData['item'] as $itemData) {
            $total += ((float)$itemData['individualItemPrice'] * (int)$itemData['itemQuantity']);
        }
        $total += (float)$orderData['shippingPrice'];
        $total -= (float)$orderData['totalDiscount'];
        return $total;
    }

    protected function copyBillingAddressToShippingAddress(array &$orderData)
    {
        unset($orderData['shippingAddressSameAsBilling']);
        foreach ($orderData as $field => $value) {
            if (!preg_match('/^billing/', $field)) {
                continue;
            }
            $shippingField = str_replace('billing', 'shipping', $field);
            $orderData[$shippingField] = $value;
        }
    }

    protected function ensureCountryCodes(array &$orderData)
    {
        if (!isset($orderData['billingAddressCountryCode']) || trim($orderData['billingAddressCountryCode']) == '') {
            $orderData['billingAddressCountryCode'] = CountryNameByCode::getCountryCodeFromName($orderData['billingAddressCountry']);
        }
        if (!isset($orderData['shippingAddressCountryCode']) || trim($orderData['shippingAddressCountryCode']) == '') {
            $orderData['shippingAddressCountryCode'] = CountryNameByCode::getCountryCodeFromName($orderData['shippingAddressCountry']);
        }
    }

    protected function createItems(array $itemsData, Order $order)
    {
        $count = 0;
        $collection = new OrderItemCollection(OrderItem::class, 'fetchCollectionByOrderId', ['orderId' => $order->getId()]);
        $products = $this->fetchProductsForItemsData($itemsData);
        foreach ($itemsData as $itemData) {
            $count++;
            $product = $products->getById($itemData['productId']);
            $item = $this->createItem($itemData, $order, $product, $count);
            $collection->attach($item);
        }

        $this->saveCollectionHandleErrors($this->orderItemService, $collection);
        $order->setItems($collection);

        return $this;
    }

    protected function createItem(array $itemData, Order $order, Product $product, $index)
    {
        $itemData['id'] = $order->getId() . '-' . $index;
        $itemData['externalId'] = $order->getExternalId() . '-' . $index;
        $itemData['orderId'] = $order->getId();
        $itemData['accountId'] = $order->getAccountId();
        $itemData['organisationUnitId'] = $order->getOrganisationUnitId();
        $itemData['purchaseDate'] = $order->getPurchaseDate();
        $itemData['status'] = $order->getStatus();
        $itemData['stockManaged'] = true;
        $itemData['individualItemDiscountPrice'] = 0;
        $itemData['itemVariationAttribute'] = $product->getAttributeValues();
        $itemData['imageIds'] = array_column($product->getImageIds(), 'id', 'order');
        if (!isset($itemData['itemName'])) {
            $itemData['itemName'] = $product->getName();
        }
        if (!isset($itemData['itemSku'])) {
            $itemData['itemSku'] = $product->getSku();
        }

        // This has to exist in the array but we don't know what it is yet, we'll recalculate it later
        $itemData['itemTaxPercentage'] = 0;
        $item = $this->orderItemMapper->fromArray($itemData);

        $taxPercentage = $this->calculateItemTaxPercentage($item, $order);
        $item->setItemTaxPercentage($taxPercentage)
            ->setCalculatedTaxPercentage($taxPercentage);

        return $item;
    }

    protected function fetchProductsForItemsData(array $itemsData)
    {
        $productIds = array_column($itemsData, 'productId');
        $filter = (new ProductFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setId($productIds);
        return $this->productService->fetchCollectionByFilter($filter);
    }

    protected function calculateItemTaxPercentage(OrderItem $item, Order $order)
    {
        try {
            $rate = $this->taxService->getRateForItemAndCountry(
                $item,
                $order->getCountryCode()
            );
            return $rate->getActive();

        } catch (NotFound $e) {
            return 0;
        }
    }

    protected function createAlert($alertMessage, Order $order)
    {
        if (trim($alertMessage) == '') {
            return $this;
        }

        $alert = $this->orderAlertMapper->fromArray([
            'userId' => $this->activeUserContainer->getActiveUser()->getId(),
            'alert' => $alertMessage,
            'timestamp' => (new StdlibDateTime())->stdFormat(),
            'orderId' => $order->getId(),
            'organisationUnitId' => $order->getOrganisationUnitId()
        ]);
        $this->orderAlertService->save($alert);

        return $this;
    }

    protected function createNotes(array $notes, Order $order)
    {
        if (empty($notes)) {
            return $this;
        }

        foreach ($notes as $noteMessage) {
            $note = $this->orderNoteMapper->fromArray([
                'orderId' => $order->getId(),
                'userId' => $this->activeUserContainer->getActiveUser()->getId(),
                'timestamp' => (new StdlibDateTime())->stdFormat(),
                'note' => $noteMessage,
                'organisationUnitId' => $order->getOrganisationUnitId()
            ]);
            $this->orderNoteService->save($note);
        }

        return $this;
    }

    // This is required to satisfy SaveCollectionHandleErrorsTrait but we shouldn't get conflicts as we're creating a new order
    protected function reapplyChangesToEntityAfterConflict($fetchedEntity, $passedEntity)
    {
        $passedEntity->setStoredETag($fetchedEntity->getStoredETag());
        return $passedEntity;
    }

    protected function setManualOrderAccountService(ManualOrderAccountService $manualOrderAccountService)
    {
        $this->manualOrderAccountService = $manualOrderAccountService;
        return $this;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    protected function setOrderItemService(OrderItemService $orderItemService)
    {
        $this->orderItemService = $orderItemService;
        return $this;
    }

    protected function setOrderAlertService(OrderAlertService $orderAlertService)
    {
        $this->orderAlertService = $orderAlertService;
        return $this;
    }

    protected function setOrderNoteService(OrderNoteService $orderNoteService)
    {
        $this->orderNoteService = $orderNoteService;
        return $this;
    }

    protected function setOrderMapper(OrderMapper $orderMapper)
    {
        $this->orderMapper = $orderMapper;
        return $this;
    }

    protected function setOrderItemMapper(OrderItemMapper $orderItemMapper)
    {
        $this->orderItemMapper = $orderItemMapper;
        return $this;
    }

    protected function setOrderAlertMapper(OrderAlertMapper $orderAlertMapper)
    {
        $this->orderAlertMapper = $orderAlertMapper;
        return $this;
    }

    protected function setOrderNoteMapper(OrderNoteMapper $orderNoteMapper)
    {
        $this->orderNoteMapper = $orderNoteMapper;
        return $this;
    }

    protected function setTaxService(TaxService $taxService)
    {
        $this->taxService = $taxService;
        return $this;
    }

    protected function setCurrencyService(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    protected function setConversionService(ConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
        return $this;
    }

    protected function setUserOuService(UserOuService $userOuService)
    {
        $this->userOuService = $userOuService;
        return $this;
    }
}