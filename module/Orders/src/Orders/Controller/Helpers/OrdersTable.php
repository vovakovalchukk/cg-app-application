<?php
namespace Orders\Controller\Helpers;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as Accounts;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\CourierTrackingUrl;
use CG\Channel\Type as ChannelType;
use CG\Order\Client\Collection as FilteredCollection;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\Item\GiftWrap\Collection as GiftWraps;
use CG\Order\Shared\Item\GiftWrap\Entity as GiftWrap;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG\User\Entity as User;
use CG_UI\View\Helper\DateFormat as DateFormatHelper;
use Orders\Order\Service as OrderService;
use Orders\Order\TableService\OrdersTableUserPreferences;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Mvc\MvcEvent;

class OrdersTable
{
    const ACCOUNTS_PAGE = 1;
    const ACCOUNTS_LIMIT = 'all';
    const MAX_SHIPPING_METHOD_LENGTH = 15;

    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var OrganisationUnitService $organisationUnitService */
    protected $organisationUnitService;
    /** @var ShippingConversionService $shippingConversionService */
    protected $shippingConversionService;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var OrderService $orderService */
    protected $orderService;
    /** @var DateFormatHelper $dateFormatHelper */
    protected $dateFormatHelper;
    /** @var OrdersTableUserPreferences $orderTableUserPreferences */
    protected $orderTableUserPreferences;
    /** @var CourierTrackingUrl $courierTrackingUrl */
    protected $courierTrackingUrl;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        OrganisationUnitService $organisationUnitService,
        ShippingConversionService $shippingConversionService,
        AccountService $accountService,
        OrderService $orderService,
        DateFormatHelper $dateFormatHelper,
        OrdersTableUserPreferences $orderTableUserPreferences,
        CourierTrackingUrl $courierTrackingUrl
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->organisationUnitService = $organisationUnitService;
        $this->shippingConversionService = $shippingConversionService;
        $this->accountService = $accountService;
        $this->orderService = $orderService;
        $this->dateFormatHelper = $dateFormatHelper;
        $this->orderTableUserPreferences = $orderTableUserPreferences;
        $this->courierTrackingUrl = $courierTrackingUrl;
    }

    public function mapOrdersCollectionToArray(Orders $orderCollection, MvcEvent $event)
    {
        $orders = $orderCollection->toArray();
        $this
            ->mapShippingMethodToAliaseName($orders)
            ->mapAccountIdToAccount($orders, $event)
            ->mapOrderStatuses($orders)
            ->truncatedShippingMethods($orders)
            ->formatDates($orders)
            ->mapGiftMessages($orderCollection, $orders)
            ->mapImageIdsToImages($orders)
            ->mapTrackingUrls($orders)
            ->mapLabelData($orders)
            ->mapLinkedOrdersData($orderCollection, $orders);

        $filterId = null;
        if ($orderCollection instanceof FilteredCollection) {
            $filterId = $orderCollection->getFilterId();
        }

        return [
            'orders' => $orders,
            'orderTotal' => (int) $orderCollection->getTotal(),
            'filterId' => $filterId,
        ];
    }

    /**
     * @return self
     */
    protected function mapShippingMethodToAliaseName(array &$orders)
    {
        try {
            $organisationUnit = $this->organisationUnitService
                ->fetch(
                    $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
                );
        } catch (NotFound $exception) {
            return $this;
        }

        foreach ($orders as &$order) {
            $shippingAlias = $this->shippingConversionService
                ->fromMethodToAlias(
                    $order['shippingMethod'],
                    $organisationUnit
                );
            $order['shippingMethod'] = $shippingAlias ? $shippingAlias->getName() : $order['shippingMethod'];
        }

        return $this;
    }

    /**
     * @return self
     */
    protected function mapAccountIdToAccount(array &$orders, MvcEvent $event)
    {
        try {
            /** @var Accounts $accounts */
            $accounts = $this->accountService->fetchByOUAndStatus(
                $this->getActiveUser()->getOuList(),
                null,
                null,
                static::ACCOUNTS_LIMIT,
                static::ACCOUNTS_PAGE,
                ChannelType::SALES
            );
        } catch (NotFound $exception) {
            return $this;
        }

        foreach ($orders as &$order) {
            $accountEntity = $accounts->getById($order['accountId']);
            if ($accountEntity instanceof Account) {
                $order['accountName'] = $accountEntity->getDisplayName();
                $order['channelImgUrl'] = $accountEntity->getImageUrl();
            }

            $order['accountLink'] = $event->getRouter()->assemble(
                ['account' => $order['accountId'], 'type' => ChannelType::SALES],
                ['name' => SettingsModule::ROUTE . '/' . ChannelController::ROUTE . '/' .ChannelController::ROUTE_CHANNELS.'/'. ChannelController::ROUTE_ACCOUNT]
            );
        }

        return $this;
    }

    /**
     * @return self
     */
    protected function mapOrderStatuses(array &$orders)
    {
        $statuses = [];
        foreach ($orders as &$order) {
            $statuses[$order['id']] = $order['status'];
        }

        $statusMessages = $this->orderService->getStatusMessageForOrders($statuses);
        foreach ($orders as &$order) {
            $order['status'] = str_replace(['_', '-'], ' ', $order['status']);
            $order['statusClass'] = str_replace(' ', '-', $order['status']);
            $order['message'] = $statusMessages[$order['id']];
        }

        return $this;
    }

    /**
     * @return self
     */
    protected function truncatedShippingMethods(array &$orders)
    {
        $ellipsis = '...';
        $ellipsisLen = strlen($ellipsis);

        foreach ($orders as &$order) {
            if (strlen($order['shippingMethod']) <= (static::MAX_SHIPPING_METHOD_LENGTH + $ellipsisLen)) {
                continue;
            }
            $order['shippingMethod'] = substr($order['shippingMethod'], 0, static::MAX_SHIPPING_METHOD_LENGTH) . $ellipsis;
        }

        return $this;
    }

    /**
     * @return self
     */
    protected function formatDates(array &$orders)
    {
        $dateFormatter = $this->dateFormatHelper;
        foreach ($orders as &$order) {
            // Keep the dates in Y-m-d H:i:s, the Mustache template will change them to a human-friendly format
            $order['purchaseDate'] = $dateFormatter($order['purchaseDate'], StdlibDateTime::FORMAT);
            $order['paymentDate'] = $dateFormatter($order['paymentDate'], StdlibDateTime::FORMAT);
            $order['printedDate'] = $dateFormatter($order['printedDate'], StdlibDateTime::FORMAT);
            $order['dispatchDate'] = $dateFormatter($order['dispatchDate'], StdlibDateTime::FORMAT);
            $order['emailDate'] = $dateFormatter($order['emailDate'], StdlibDateTime::FORMAT);
        }
        return $this;
    }

    /**
     * @return self
     */
    protected function mapGiftMessages(Orders $orderCollection, &$orders)
    {
        foreach ($orders as &$order) {
            $giftMessages = [];

            /** @var Order|null $orderEntity */
            $orderEntity = $orderCollection->getById($order['id']);
            if (!$orderEntity) {
                continue;
            }

            /** @var Item $orderItemEntity */
            foreach ($orderEntity->getItems() as $orderItemEntity) {
                /** @var GiftWraps $giftWraps */
                $giftWraps = $orderItemEntity->getGiftWraps();
                $giftWraps->rewind();

                /** @var GiftWrap $giftWrap */
                foreach ($giftWraps as $giftWrap) {
                    $giftMessages[] = [
                        'type' => $giftWrap->getGiftWrapType(),
                        'message' => $giftWrap->getGiftWrapMessage(),
                    ];
                }
            }

            $order['giftMessageCount'] = count($giftMessages);
            $order['giftMessages'] = json_encode($giftMessages);
        }

        return $this;
    }

    /**
     * @return self
     */
    protected function mapImageIdsToImages(array &$orders)
    {
        $columns = $this->orderTableUserPreferences->fetchUserPrefOrderColumns();
        if (!isset($columns['image']) || filter_var($columns['image'], FILTER_VALIDATE_BOOLEAN) == false) {
            return $this;
        }

        $imagesToFetch = [];
        foreach ($orders as $index => $order) {
            $orders[$index]['image'] = '';
            if (empty($order['items']) || empty($order['items'][0]['imageIds'])) {
                continue;
            }
            $imagesToFetch[$index] = $order['items'][0]['imageIds'][0];
        }

        if (empty($imagesToFetch)) {
            return $this;
        }

        try {
            $images = $this->orderService->fetchImagesById(array_values($imagesToFetch));
        } catch (NotFound $exception) {
            return $this;
        }

        foreach ($imagesToFetch as $orderIndex => $imageId) {
            $image = $images->getById($imageId);
            if (!$image) {
                continue;
            }
            $orders[$orderIndex]['image'] = $image->getUrl();
        }

        return $this;
    }

    /**
     * @return self
     */
    protected function mapTrackingUrls(array &$orders)
    {
        foreach ($orders as &$order) {
            foreach ($order['trackings'] as $i => $tracking) {
                $order['trackings'][$i]['trackingUrl'] = $this->courierTrackingUrl->getTrackingUrl($tracking['carrier'], $tracking['number']);
            }
        }
        return $this;
    }

    /**
     * @return self
     */
    protected function mapLabelData(array &$orders)
    {
        foreach ($orders as &$order) {
            $order['labelCreatedDate'] = '';
        }
        return $this;
    }

    protected function mapLinkedOrdersData(Orders $orderCollection, &$orders)
    {
        $orderIds = [];
        foreach ($orders as $order) {
            $orderIds[] = $order['id'];
        }

        $linkedOrders = $this->orderService->getLinkedOrdersData($orderCollection);

        foreach ($orders as &$order) {
            if (isset($linkedOrders[$order['id']])) {
                $order['linkedOrdersData'] = [
                    'linkedOrders' => $linkedOrders[$order['id']],
                ];
            }
        }
        return $this;
    }

    /**
     * @return User
     */
    protected function getActiveUser()
    {
        return $this->activeUserContainer->getActiveUser();
    }
}
