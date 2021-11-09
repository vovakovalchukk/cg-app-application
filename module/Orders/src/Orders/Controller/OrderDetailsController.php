<?php
namespace Orders\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Amazon\Order\FulfilmentChannel\Mapper as FulfilmentChannelMapper;
use CG\Locale\EUCountryNameByVATCode;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\PartialRefund\Service as PartialRefundService;
use CG\Order\Shared\Tax\Destination;
use CG\Order\Shared\Tax\Origin;
use CG\Order\Shared\Tracking\Entity as OrderTracking;
use CG\Order\Shared\Tracking\Mapper as OrderTrackingMapper;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use CG_Access\UsageExceeded\Service as AccessUsageExceededService;
use CG_UI\View\Prototyper\ViewModelFactory;
use Messages\Module as Messages;
use Orders\Controller\Helpers\Courier as CourierHelper;
use Orders\Controller\Helpers\OrderNotes as OrderNotesHelper;
use Orders\Module;
use Orders\Order\BulkActions\Service as BulkActionsService;
use Orders\Order\Service as OrderService;
use Orders\Order\Timeline\Service as TimelineService;
use Settings\Controller\ChannelController as ChannelSettings;
use Settings\Module as Settings;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class OrderDetailsController extends AbstractActionController
{
    /** @var CourierHelper $courierHelper */
    protected $courierHelper;
    /** @var OrderService $orderService */
    protected $orderService;
    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var BulkActionsService $bulkActionsService */
    protected $bulkActionsService;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var TimelineService $timelineService */
    protected $timelineService;
    /** @var OrderNotesHelper $orderNotesHelper */
    protected $orderNotesHelper;

    protected $orderTrackingMapper;
    /** @var AccessUsageExceededService */
    protected $accessUsageExceededService;

    protected $activeUserContainer;
    /** @var PartialRefundService */
    protected $partialRefundService;

    protected $courierNameMapper = [
        'royal-mail-click-drop' => 'Royal Mail',
    ];

    public function __construct(
        CourierHelper $courierHelper,
        OrderService $orderService,
        ViewModelFactory $viewModelFactory,
        BulkActionsService $bulkActionsService,
        AccountService $accountService,
        TimelineService $timelineService,
        OrderNotesHelper $orderNotesHelper,
        OrderTrackingMapper $orderTrackingMapper,
        AccessUsageExceededService $accessUsageExceededService,
        ActiveUserInterface $activeUserContainer,
        PartialRefundService $partialRefundService
    ) {
        $this->courierHelper = $courierHelper;
        $this->orderService = $orderService;
        $this->viewModelFactory = $viewModelFactory;
        $this->bulkActionsService = $bulkActionsService;
        $this->accountService = $accountService;
        $this->timelineService = $timelineService;
        $this->orderNotesHelper = $orderNotesHelper;
        $this->orderTrackingMapper = $orderTrackingMapper;
        $this->accessUsageExceededService = $accessUsageExceededService;
        $this->activeUserContainer = $activeUserContainer;
        $this->partialRefundService = $partialRefundService;
    }

    public function orderAction()
    {
        $this->accessUsageExceededService->checkUsage();

        /** @var Order $order */
        $order = $this->orderService->getOrder($this->params('order'));
        $order = $this->partialRefundService->addRefundLinesToOrder($order);
        $carriers = $this->getCarrierSelect($order);
        $view = $this->viewModelFactory->newInstance(
            [
                'order' => $order
            ]
        );
        $view->setTemplate('orders/orders/order');
        $bulkActions = $this->bulkActionsService->getBulkActionsForOrder($order);
        $backButton = $this->getBackButton();

        $productPaymentInfo = $this->getProductAndPaymentDetails($order);
        $labelDetails = $this->getShippingLabelDetails($order);
        $accountDetails = $this->getAccountDetails($order);
        $orderDetails = $this->getOrderDetails($order);
        $statusTemplate = $this->getStatus($order->getStatus(), $this->orderService->getStatusMessageForOrder($order->getId(), $order->getStatus()));

        $buyerMessage = $this->getBuyerMessage($order);
        $orderAlert = $this->getOrderAlert($order);
        $addressInformation = $this->getAddressInformation($order);

        $view->addChild($productPaymentInfo, 'productPaymentInfo');
        $view->addChild($labelDetails, 'labelDetails');
        $view->addChild($accountDetails, 'accountDetails');
        $view->addChild($orderDetails, 'orderDetails');
        $view->addChild($statusTemplate, 'status');
        $view->addChild($bulkActions, 'bulkActions');
        $view->addChild($backButton, 'backButton');
        $view->addChild($buyerMessage, 'buyerMessage');
        $view->addChild($orderAlert, 'orderAlert');
        $view->addChild($addressInformation, 'addressInformation');
        $view->addChild($this->getLinkedOrdersSection($order), 'linkedOrdersSection');
        $view->addChild($this->getTimelineBoxes($order), 'timelineBoxes');
        $view->addChild($this->getDetailsSidebar(), 'sidebar');
        $view->setVariable('existingNotes', $this->getNotes($order));
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        $view->setVariable('carriers', $carriers);
        $view->setVariable('editable', $this->orderService->isOrderEditable($order));

        $this->addLabelPrintButtonToView($view, $order);
        return $view;
    }

    protected function getCarrierSelect(Order $order)
    {
        $carriers = $this->courierHelper->getCarriersData();
        $tracking = $order->getFirstTracking();
        $priorityOptions = $this->courierHelper->getCarrierPriorityOptions($tracking);
        $options = [['title' => 'None', 'value' => '-', 'selected' => ($tracking == null)]];
        foreach ($carriers as $carrier) {
            $selected = false;
            if(!is_null($tracking)) {
                $selected = ($tracking->getCarrier() == $carrier);
            }
            $options[] = [
                'title' => $carrier,
                'value' => $carrier,
                'selected' => $selected
            ];
        }
        $carrierSelect = $this->viewModelFactory->newInstance(["options" => $options]);
        $carrierSelect->setTemplate("elements/custom-select.mustache");
        $carrierSelect->setVariable("name", "carrier");
        $carrierSelect->setVariable("id", "carrier");
        $carrierSelect->setVariable("blankOption", true);
        $carrierSelect->setVariable("searchField", true);
        $carrierSelect->setVariable("priorityOptions", $priorityOptions);
        return $carrierSelect;
    }

    protected function getBackButton()
    {
        $backButton = $this->viewModelFactory->newInstance();
        $backButton->setTemplate('orders/orders/bulk-actions/order');
        return $backButton;
    }

    protected function getProductAndPaymentDetails(Order $order)
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/productPaymentInfo');

        $vatOu = $this->orderService->getVatOrganisationUnitForOrder($order);
        $view->setVariable('order', $order);
        $view->setVariable('vatOu', $vatOu);

        if (empty($order->getShippingOriginCountryCode())) {
            $taxOrigin = Origin::fromCountryAndPostcode($order->getShippingOriginCountryCode());
        } else {
            $taxOrigin = Origin::fromCountryAndPostcode($vatOu->getAddressCountryCode(), $vatOu->getAddressPostcode());
        }
        $taxDestination = Destination::fromCountryAndPostcode($order->getCalculatedShippingAddressCountryCode(), $order->getCalculatedShippingAddressPostcode());
        $view->setVariable('enforceEuVat', $taxOrigin->isGB() && !$taxOrigin->isNI() && $taxDestination->isEU());

        if ($order->isEligibleForZeroRateVat() && $taxOrigin->isNI() && $taxDestination->isEU()) {
            $recipientVatNumber = $order->getRecipientVatNumber();
            $view->setVariable('isOrderZeroRated', (isset($recipientVatNumber) && strlen($recipientVatNumber)));
            $view->setVariable('vatNumber', substr($recipientVatNumber, 2));

            $view->addChild($this->getZeroRatedCheckbox($recipientVatNumber), 'zeroRatedCheckbox');
            $view->addChild($this->getRecipientVatNumberSelectbox($order, $recipientVatNumber), 'zeroRatedSelectBox');
        }

        $view->addChild($this->orderService->getOrderItemTable($order), 'productPaymentTable');

        return $view;
    }

    protected function getZeroRatedCheckbox($isOrderZeroRated)
    {
        $zeroRatedCheckbox = $this->viewModelFactory->newInstance([
            'id' => 'zero-rated-vat-checkbox',
            'name' => 'zeroRatedVatCheckbox',
            'selected' => (boolean) $isOrderZeroRated
        ]);
        $zeroRatedCheckbox->setTemplate('elements/checkbox.mustache');
        return $zeroRatedCheckbox;
    }

    protected function getRecipientVatNumberSelectbox(Order $order, $recipientVatNumber = null)
    {
        $initialValue = $order->getCalculatedShippingAddressCountryCode();
        if ($recipientVatNumber !== null && $recipientVatNumber !== '') {
            $initialValue = substr($recipientVatNumber, 0, 2);
        }
        return EUCountryNameByVATCode::getVatCodeSelectbox($this->viewModelFactory, $initialValue, 'zero-rated-vat-code-select', 'zeroRatedVatCode');
    }

    protected function getShippingLabelDetails(Order $order)
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/shippingLabelDetails');
        $view->setVariable('shippingMethod', $order->getShippingMethod());
        $view->setVariable('order', $order);

        try {
            $labels = $this->courierHelper->getNonCancelledOrderLabelsForOrders([$order->getId()]);
            $labelData = [];
            foreach ($labels as $label) {
                $labelData[] = $label->toArray();
            }

            /* @var $label \CG\Order\Shared\Label\Entity */
            $label = $labels->getFirst();

            $trackingNumbers = $this->getTrackingNumberDetails($order, $label);

            usort($trackingNumbers, function ($a, $b) {
                return ($a['packageNumber'] - $b['packageNumber']);
            });

            $view->setVariable('trackings', $trackingNumbers);
            $view->setVariable('labels', $labelData);

            if (in_array($label->getStatus(), OrderLabelStatus::getPrintableStatuses())) {
                $view->addChild($this->getPrintLabelButton($order), 'printButton');
            }
        } catch (NotFound $e) {
            $view->setVariable('trackings', []);
            $view->addChild($this->getCarrierSelect($order), 'carrierSelect');
            $view->setVariable('tracking', $order->getFirstTracking());
            $view->setVariable('trackingShippingService', $this->determineOrderTrackingShippingService($order));
        }

        return $view;
    }

    protected function determineOrderTrackingShippingService(Order $order): string
    {
        $orderTracking = $order->getFirstTracking();
        if (($orderTracking instanceof OrderTracking) && !empty($orderTracking->getShippingService())) {
            return $orderTracking->getShippingService();
        }
        return $order->getShippingMethod();
    }

    protected function getPrintLabelButton(Order $order)
    {
        $buttons = $this->viewModelFactory->newInstance([
            'buttons' => [
                'value' => 'Print',
                'id' => 'print-shipping-label-button',
                'disabled' => false,
                'action' => $order->getId(),
            ]
        ]);
        $buttons->setTemplate('elements/buttons.mustache');
        return $buttons;
    }

    protected function getTrackingNumberDetails(Order $order, OrderLabel $label): array
    {
        $trackings = $order->getTrackings();
        if ($trackings->count() > 0) {
            return $trackings->toArray();
        }

        $carrier = $this->courierNameMapper[$label->getChannelName()] ?? $label->getChannelName() ;

        $orderTracking = $this->orderTrackingMapper->fromArray(
            [
                'userId' =>  $this->activeUserContainer->getActiveUser()->getId(),
                'orderId' => $order->getId(),
                'number' => null,
                'carrier' => $carrier,
                'timestamp' => date(StdlibDateTime::FORMAT),
                'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
            ]
        );

        return [$orderTracking->toArray()];
    }

    protected function getAccountDetails(Order $order)
    {
        /** @var Account $account */
        $account = $this->accountService->fetch($order->getAccountId());
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/accountDetails');
        $view->addChild($this->getChannelLogo($account, $order), 'channelLogo');
        $view->setVariable(
            'accountUrl',
            $this->url()->fromRoute(
                implode('/', [Settings::ROUTE, ChannelSettings::ROUTE, ChannelSettings::ROUTE_CHANNELS, ChannelSettings::ROUTE_ACCOUNT]),
                ['type' => $account->getType(), 'account' => $account->getId()]
            )
        );
        $view->setVariable('accountName', $account->getDisplayName());
        return $view;
    }

    protected function getChannelLogo(Account $account, Order $order)
    {
        $channel = $account->getChannel();
        if ($account->getChannel() === 'amazon' && $order->getFulfilmentChannel() === FulfilmentChannelMapper::CG_FBA) {
            /**
             * Any change to this code should be reflected in:
             *  /module/Orders/src/Orders/Controller/Helpers/OrdersTable.php (mapAccountIdToAccount)
             */
            $channel .= '-fba';
        }
        $externalData = $account->getExternalData();
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate("elements/channel-large.mustache");
        $view->setVariable('channel', $channel);
        if (isset($externalData['imageUrl']) && !empty($externalData['imageUrl'])) {
            $view->setVariable('channelImgUrl', $externalData['imageUrl']);
        }
        return $view;
    }

    protected function getOrderDetails(Order $order)
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/orderDetails');
        $view->setVariable('orderId', $order->getExternalId());
        $view->setVariable('channel', $order->getChannel());
        $view->setVariable('buyerName', $order->getBillingAddress()->getAddressFullName());
        $view->setVariable('buyerUsername', $order->getExternalUsername());
        $view->setVariable(
            'orderUrl',
            $this->url()->fromRoute(
                Module::ROUTE,
                [],
                [
                    'query' => $this->getSearchParamsForBuyer($order)
                ]
            )
        );
        if ($order->getExternalUsername() !== null || $order->getBillingAddress()->getEmailAddress() !== null) {
            $messageUrl = $this->url()->fromRoute(
                implode('/', [Messages::ROUTE]),
                [],
                [
                    'query' => ['f' => 'eu', 'fv' => $order->getExternalUsername() ?: $order->getBillingAddress()->getEmailAddress()]
                ]
            );
        } else {
            $messageUrl = null;
        }
        $view->setVariable('messageUrl', $messageUrl);
        return $view;
    }

    protected function getSearchParamsForBuyer(Order $order): array
    {
        if ($order->getExternalUsername()) {
            return ['search' => $order->getExternalUsername(), 'searchField' => ['order.externalUsername']];
        }
        return ['search' => $order->getBillingAddress()->getEmailAddress(), 'searchField' => ['billing.emailAddress']];
    }

    protected function getStatus($statusText, $messageText)
    {
        $status = $this->viewModelFactory->newInstance();
        $status->setTemplate("columns/status.mustache");
        $status->setVariable('status', $statusText);
        $status->setVariable('message', $messageText);
        $status->setVariable('statusClass', str_replace(' ', '-', $statusText));
        return $status;
    }

    protected function getLinkedOrdersSection(Order $order)
    {
        $orders = new OrderCollection(Order::class, 'fetch', ['id' => $order->getId()]);
        $orders->attach($order);
        $linkedOrders = $this->orderService->getLinkedOrdersData($orders);

        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/linkedOrders');
        $view->setVariable('linkedOrders', ($linkedOrders[$order->getId()] ?? null));
        return $view;
    }

    protected function getBuyerMessage(Order $order)
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/buyerMessage');
        if (! $order) {
            return $view;
        }
        $view->setVariable('buyerMessage', $order->getBuyerMessage() ?: $this->translate("There is no buyer message for this order"));
        return $view;
    }

    protected function getOrderAlert(Order $order)
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/orderAlert');
        if (! $order) {
            return $view;
        }
        $view->setVariable('order', $order);
        return $view;
    }

    protected function getAddressInformation(Order $order)
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/addressInformation');
        $view->setVariable('order', $order);
        $view->setVariable('addressSaveUrl', 'Orders/order/address');
        $view->setVariable('billingAddressEditable', $this->orderService->isBillingAddressEditable($order));
        $view->setVariable('shippingAddressEditable', $this->orderService->isShippingAddressEditable($order));
        $view->setVariable('requiresSaveButton', true);
        $view->setVariable('includeAddressCopy', true);
        $view->setVariable('includeUseBillingInfo', false);
        return $view;
    }

    protected function getTimelineBoxes(Order $order)
    {
        $timelineBoxes = $this->viewModelFactory->newInstance(
            $this->timelineService->getTimeline($order)
        );
        $timelineBoxes->setTemplate('elements/timeline-boxes');
        return $timelineBoxes;
    }

    protected function getDetailsSidebar()
    {
        $sidebar = $this->viewModelFactory->newInstance();
        $sidebar->setTemplate('orders/orders/sidebar/navbar');

        $links = [
            'timeline' => 'Timeline',
            'order-alert' => 'Alert',
            'order-buyer-message' => 'Buyer Message',
            'addressInformation' => 'Address Information',
            'tracking-information' => 'Shipping',
            'product-payment-table' => 'Payment Information',
            'order-notes' => 'Notes'

        ];
        $sidebar->setVariable('links', $links);

        return $sidebar;
    }

    protected function getNotes(Order $order)
    {
        $existingNotes = $this->orderNotesHelper->getNamesFromOrderNotes($order->getNotes());
        usort($existingNotes, function ($a, $b) {
            return strtotime($a['timestamp']) > strtotime($b['timestamp']);
        });
        return json_encode($existingNotes);
    }

    protected function addLabelPrintButtonToView(ViewModel $view, Order $order)
    {
        try {
            $this->courierHelper->getPrintableOrderLabelForOrder($order);
        } catch (NotFound $e) {
            return;
        }
        $buttons = $this->getPrintLabelButton($order);
        $view->addChild($buttons, 'printShippingLabelButton');
    }
}
