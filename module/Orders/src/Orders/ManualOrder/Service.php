<?php
namespace Orders\ManualOrder;

use CG\Account\Shared\Entity as Account;
use CG\Http\SaveCollectionHandleErrorsTrait;
use CG\ManualOrder\Account\Service as ManualOrderAccountService;
use CG\Order\Client\Alert\Service as OrderAlertService;
use CG\Order\Client\Item\Service as OrderItemService;
use CG\Order\Client\Note\Service as OrderNoteService;
use CG\Order\Client\Service as OrderService;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Collection as OrderItemCollection;
use CG\Order\Shared\Item\Entity as OrderItem;
use CG\Order\Shared\Item\Mapper as OrderItemMapper;
use CG\Order\Shared\Mapper as OrderMapper;
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
    /** @var TaxService */
    protected $taxService;

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
        TaxService $taxService
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
            ->setTaxService($taxService);
    }

    public function createOrderFromPostData(array $orderData)
    {
        $organisationUnitId = $this->getOrganisationUnitIdForOrderCreation($orderData);
        $organisationUnit = $this->organisationUnitService->fetch($organisationUnitId);
        $account = $this->manualOrderAccountService->getAccountForOrganisationUnit($organisationUnit);

        $order = $this->createOrder($orderData, $account);
        $this->createItems($orderData['Item'], $order);
        // TODO: alert, notes
    }
    
    protected function getOrganisationUnitIdForOrderCreation(array $orderData)
    {
        if (isset($orderData['organisationUnitId'])) {
            return $orderData['organisationUnitId'];
        }
        return $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
    }

    protected function createOrder(array $orderData, Account $account)
    {
        unset($orderData['item'], $orderData['alert'], $orderData['note']);
        $orderData['accountId'] = $account->getId();
        $orderData['id'] = $this->manualOrderAccountService->getNextOrderIdForAccount($account);
        $orderData['channel'] = $account->getChannel();
        $orderData['organisationUnitId'] = $account->getOrganisationUnitId();
        $orderData['status'] = OrderStatus::AWAITING_PAYMENT;
        $orderData['purchaseDate'] = (new StdlibDateTime())->stdFormat();
        // TODO: get this from the UI?
        $orderData['currencyCode'] = 'GBP';

        if (isset($orderData['shippingAddressSameAsBilling']) && (bool)$orderData['shippingAddressSameAsBilling'] == true) {
            $this->copyBillingAddressToShippingAddress($orderData);
        }

        $order = $this->orderMapper->fromArray($orderData);
        return $this->orderService->save($order);
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

    protected function createItems(array $itemsData, Order $order)
    {
        $count = 0;
        $collection = new OrderItemCollection(OrderItem::class, 'fetchCollectionByOrderId', ['orderId' => $order->getId()]);
        $products = $this->fetchProductsForItemsData($itemsData);
        foreach ($itemsData as $itemData) {
            $count++;
            $itemProducts = $products->getById($itemData['productId']);
            $itemProducts->rewind();
            $item = $this->createItem($itemData, $order, $itemProducts->current(), $count);
            $collection->attach($item);
        }

        $this->saveCollectionHandleErrors($this->orderItemService, $collection);
        $order->setItems($collection);
    }

    protected function createItem(array $itemData, Order $order, Product $product, $index)
    {
        $itemData['id'] = $order->getId() . '-' . $index;
        $itemData['orderId'] = $order->getId();
        $itemData['accountId'] = $order->getAccountId();
        $itemData['organisationUnitId'] = $order->getOrganisationUnitId();
        $itemData['purchaseDate'] = $order->getPurchaseDate();
        $itemData['status'] = $order->getStatus();
        $itemData['stockManaged'] = true;
        $itemData['itemVariationAttribute'] = $product->getAttributeValues();
        if (!isset($itemData['itemName'])) {
            $itemData['itemName'] = $product->getName();
        }
        if (!isset($itemData['itemSku'])) {
            $itemData['itemSku'] = $product->getSku();
        }

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

    protected function setTaxService(TaxService $taxService)
    {
        $this->taxService = $taxService;
        return $this;
    }
}