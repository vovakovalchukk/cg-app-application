<?php
namespace Orders\ManualOrder;

use CG\Account\Shared\Entity as Account;
use CG\ManualOrder\Account\Service as ManualOrderAccountService;
use CG\Order\Client\Alert\Service as OrderAlertService;
use CG\Order\Client\Item\Service as OrderItemService;
use CG\Order\Client\Note\Service as OrderNoteService;
use CG\Order\Client\Service as OrderService;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Order\Shared\Status as OrderStatus;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\Client\Service as ProductService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;

class Service implements LoggerAwareInterface
{
    use LogTrait;

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

    public function __construct(
        ManualOrderAccountService $manualOrderAccountService,
        ActiveUserInterface $activeUserContainer,
        OrganisationUnitService $organisationUnitService,
        ProductService $productService,
        OrderService $orderService,
        OrderItemService $orderItemService,
        OrderAlertService $orderAlertService,
        OrderNoteService $orderNoteService,
        OrderMapper $orderMapper
    ) {
        $this->setManualOrderAccountService($manualOrderAccountService)
            ->setActiveUserContainer($activeUserContainer)
            ->setOrganisationUnitService($organisationUnitService)
            ->setProductService($productService)
            ->setOrderService($orderService)
            ->setOrderItemService($orderItemService)
            ->setOrderAlertService($orderAlertService)
            ->setOrderNoteService($orderNoteService)
            ->setOrderMapper($orderMapper);
    }

    public function createOrderFromPostData(array $orderData)
    {
        $organisationUnitId = $this->getOrganisationUnitIdForOrderCreation($orderData);
        $organisationUnit = $this->organisationUnitService->fetch($organisationUnitId);
        $account = $this->manualOrderAccountService->getAccountForOrganisationUnit($organisationUnit);

        $order = $this->createOrder($orderData, $account);
        // TODO: items, alert, notes
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
}