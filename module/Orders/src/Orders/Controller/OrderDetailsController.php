<?php
namespace Orders\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Locale\EUCountryNameByVATCode;
use CG\Order\Shared\Entity as Order;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\ViewModelFactory;
use Messages\Module as Messages;
use Orders\Controller\Helpers\Courier as CourierHelper;
use Orders\Controller\Helpers\Usage as UsageHelper;
use Orders\Controller\Helpers\OrderNotes as OrderNotesHelper;
use Orders\Module;
use Orders\Order\BulkActions\Action\Courier as CourierBulkAction;
use Orders\Order\BulkActions\Service as BulkActionsService;
use Orders\Order\Service as OrderService;
use Orders\Order\Timeline\Service as TimelineService;
use Settings\Controller\ChannelController as ChannelSettings;
use Settings\Module as Settings;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class OrderDetailsController extends AbstractActionController
{
    /** @var UsageHelper $usageHelper */
    protected $usageHelper;
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

    public function __construct(
        UsageHelper $usageHelper,
        CourierHelper $courierHelper,
        OrderService $orderService,
        ViewModelFactory $viewModelFactory,
        BulkActionsService $bulkActionsService,
        AccountService $accountService,
        TimelineService $timelineService,
        OrderNotesHelper $orderNotesHelper
    ) {
        $this->usageHelper = $usageHelper;
        $this->courierHelper = $courierHelper;
        $this->orderService = $orderService;
        $this->viewModelFactory = $viewModelFactory;
        $this->bulkActionsService = $bulkActionsService;
        $this->accountService = $accountService;
        $this->timelineService = $timelineService;
        $this->orderNotesHelper = $orderNotesHelper;
    }

    public function orderAction()
    {
        $this->usageHelper->checkUsage();

        /** @var Order $order */
        $order = $this->orderService->getOrder($this->params('order'));
        $carriers = $this->getCarrierSelect($order);
        $view = $this->viewModelFactory->newInstance(
            [
                'order' => $order
            ]
        );
        $view->setTemplate('orders/orders/order');
        $bulkActions = $this->getBulkActionsForOrder($order);
        $bulkActions->addChild(
            $this->viewModelFactory->newInstance()->setTemplate('orders/orders/bulk-actions/order'),
            'afterActions'
        );

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
        $view->addChild($buyerMessage, 'buyerMessage');
        $view->addChild($orderAlert, 'orderAlert');
        $view->addChild($addressInformation, 'addressInformation');
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
        $priorityOptions = $this->courierHelper->getCarrierPriorityOptions();
        $carriers = $this->courierHelper->getCarriersData();
        $tracking = $order->getFirstTracking();
        $options = [];
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

    protected function getBulkActionsForOrder(Order $order)
    {
        $bulkActions = $this->bulkActionsService->getOrderBulkActions($order);
        if ($this->courierHelper->hasCourierAccounts()) {
            return $bulkActions;
        }
        foreach ($bulkActions->getActions() as $action) {
            if (!($action instanceof CourierBulkAction)) {
                continue;
            }
            $bulkActions->getActions()->detach($action);
        }
        return $bulkActions;
    }

    protected function getProductAndPaymentDetails(Order $order)
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/productPaymentInfo');


        $view->setVariable('order', $order);
        $view->setVariable('vatOu', $this->orderService->getVatOrganisationUnitForOrder($order));

        if ($order->isEligibleForZeroRateVat()) {
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

            $trackingNumbers = $order->getTrackings()->toArray();
            usort($trackingNumbers, function ($a, $b) {
                return ($a['packageNumber'] - $b['packageNumber']);
            });

            $view->setVariable('trackings', $trackingNumbers);
            $view->setVariable('labels', $labelData);
            $view->addChild($this->getPrintLabelButton($order), 'printButton');
        } catch (NotFound $e) {
            $view->addChild($this->getCarrierSelect($order), 'carrierSelect');
            $view->setVariable('tracking', $order->getFirstTracking());
        }

        return $view;
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

    protected function getAccountDetails(Order $order)
    {
        /** @var Account $account */
        $account = $this->accountService->fetch($order->getAccountId());
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('orders/orders/order/accountDetails');
        $view->addChild($this->getChannelLogo($account), 'channelLogo');
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

    protected function getChannelLogo(Account $account)
    {
        $externalData = $account->getExternalData();
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate("elements/channel-large.mustache");
        $view->setVariable('channel', $account->getChannel());
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
                    'query' => ['search' => $order->getExternalUsername() ?: $order->getBillingAddress()->getEmailAddress()]
                ]
            )
        );
        $view->setVariable(
            'messageUrl',
            $this->url()->fromRoute(
                implode('/', [Messages::ROUTE]),
                [],
                [
                    'query' => ['f' => 'eu', 'fv' => $order->getExternalUsername() ?: $order->getBillingAddress()->getEmailAddress()]
                ]
            )
        );
        return $view;
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
