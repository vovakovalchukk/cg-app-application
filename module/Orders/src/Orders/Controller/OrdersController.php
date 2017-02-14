<?php
namespace Orders\Controller;

use ArrayObject;
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Locale\EUCountryNameByVATCode;
use CG\Order\Service\Filter;
use CG\Order\Shared\Entity as OrderEntity;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\OrderCounts\Storage\Api as OrderCountsApi;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\OrderBy;
use CG\Stdlib\PageLimit;
use CG\User\ActiveUserInterface;
use CG_UI\View\BulkActions as BulkActionsViewModel;
use CG_UI\View\Filters\Service as UIFiltersService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use CG_Usage\Service as UsageService;
use Orders\Courier\Manifest\Service as ManifestService;
use Orders\Courier\Service as CourierService;
use Orders\Filter\DisplayFilter;
use Orders\Filter\Service as FilterService;
use Orders\Module;
use Orders\Order\Batch\Service as BatchService;
use Orders\Order\BulkActions\Action\Courier as CourierBulkAction;
use Orders\Order\BulkActions\Service as BulkActionsService;
use Orders\Order\BulkActions\SubAction\CourierManifest as CourierManifestBulkAction;
use Orders\Order\Service as OrderService;
use Orders\Order\StoredFilters\Service as StoredFiltersService;
use Orders\Order\Timeline\Service as TimelineService;
use Settings\Controller\ChannelController as ChannelSettings;
use Settings\Module as Settings;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\I18n\View\Helper\CurrencyFormat;
use Messages\Module as Messages;

class OrdersController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_INDEX_URL = '/orders';
    const ROUTE_IMAGES = 'Images';

    const FILTER_SHIPPING_METHOD_NAME = "shippingMethod";
    const FILTER_SHIPPING_ALIAS_NAME = "shippingAliasId";
    const FILTER_TYPE = "orders";

    /** @var OrderService $orderService */
    protected $orderService;
    /** @var FilterService $filterService */
    protected $filterService;
    /** @var TimelineService $timelineService */
    protected $timelineService;
    /** @var BatchService $batchService */
    protected $batchService;
    /** @var BulkActionsService $bulkActionsService */
    protected $bulkActionsService;
    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;
    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var UIFiltersService $uiFiltersService */
    protected $uiFiltersService;
    /** @var StoredFiltersService $storedFiltersService */
    protected $storedFiltersService;
    /** @var UsageService $usageService */
    protected $usageService;
    /** @var ShippingConversionService $shippingConversionService */
    protected $shippingConversionService;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var OrderCountsApi $orderCountsApi */
    protected $orderCountsApi;
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;

    /** @var OrderLabelService */
    protected $orderLabelService;
    /** @var ManifestService */
    protected $manifestService;
    /** @var CourierService */
    protected $courierService;
    /** @var CurrencyFormat */
    protected $currencyFormat;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        OrderService $orderService,
        FilterService $filterService,
        TimelineService $timelineService,
        BatchService $batchService,
        BulkActionsService $bulkActionsService,
        UIFiltersService $uiFiltersService,
        StoredFiltersService $storedFiltersService,
        UsageService $usageService,
        ShippingConversionService $shippingConversionService,
        AccountService $accountService,
        OrderCountsApi $orderCountsApi,
        ActiveUserInterface $activeUserContainer,
        OrderLabelService $orderLabelService,
        ManifestService $manifestService,
        CourierService $courierService,
        CurrencyFormat $currencyFormat
    ) {
        $this
            ->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setOrderService($orderService)
            ->setFilterService($filterService)
            ->setTimelineService($timelineService)
            ->setBatchService($batchService)
            ->setBulkActionsService($bulkActionsService)
            ->setUIFiltersService($uiFiltersService)
            ->setStoredFiltersService($storedFiltersService)
            ->setUsageService($usageService)
            ->setShippingConversionService($shippingConversionService)
            ->setAccountService($accountService)
            ->setOrderCountsApi($orderCountsApi)
            ->setActiveUserContainer($activeUserContainer)
            ->setOrderLabelService($orderLabelService)
            ->setManifestService($manifestService)
            ->setCourierService($courierService);

        $this->currencyFormat = $currencyFormat;
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $ordersTable = $this->getOrderService()->getOrdersTable();

        if ($searchTerm = $this->params()->fromQuery('search')) {
            $filterValues = [
                'searchTerm' => $searchTerm,
                'archived' => [true, false],
            ];
        } else {
            $filterValues = $this->getFilterService()->getMapper()->toArray(
                $this->getFilterService()->getPersistentFilter()->getFilter()
            );
        }
        if (isset($filterValues['purchaseDate']['from'])) {
            $filterValues['purchaseDate']['from'] = $this->dateFormatOutput($filterValues['purchaseDate']['from'], StdlibDateTime::FORMAT);
        }
        if (isset($filterValues['purchaseDate']['to'])) {
            $filterValues['purchaseDate']['to'] = $this->dateFormatOutput($filterValues['purchaseDate']['to'], StdlibDateTime::FORMAT);
        }
        $ordersTable->setVariable('filterValues', $filterValues);
        $settings = $ordersTable->getVariable('settings');
        $settings->setSource($this->url()->fromRoute('Orders/ajax'));
        $settings->setTemplateUrlMap($this->mustacheTemplateMap('orderList'));
        $view->addChild($ordersTable, 'ordersTable');
        $bulkActions = $this->getBulkActionsViewModel();
        $bulkAction = $this->getViewModelFactory()->newInstance()->setTemplate('orders/orders/bulk-actions/index');
        $bulkAction->setVariable('isHeaderBarVisible', $this->getOrderService()->isFilterBarVisible());
        $bulkActions->addChild(
            $bulkAction,
            'afterActions'
        );

        $view->addChild($bulkActions, 'bulkItems');
        $view->addChild($this->getFilterBar(), 'filters');
        $view->addChild($this->getStatusFilters(), 'statusFiltersSidebar');
        $view->addChild(
            $this->getStoredFiltersService()->getStoredFiltersSidebarView(
                $this->getOrderService()->getActiveUserPreference()
            ),
            'storedFiltersSidebar'
        );
        $view->addChild($this->getBatches(), 'batches');
        $view->setVariable('isSidebarVisible', $this->getOrderService()->isSidebarVisible());
        $view->setVariable('isHeaderBarVisible', $this->getOrderService()->isFilterBarVisible());
        $view->setVariable('filterNames', $this->getUIFiltersService()->getFilterNames(static::FILTER_TYPE));
        return $view;
    }

    protected function getStatusFilters()
    {
        $view = $this->getViewModelFactory()->newInstance(
            [
                'filters' => $this->getUIFiltersService()->getFilterConfig('stateFilters')
            ]
        );
        $view->setTemplate('orders/orders/sidebar/statusFilters');
        return $view;
    }

    protected function getBulkActionsViewModel()
    {
        $bulkActionsViewModel = $this->getBulkActionsService()->getBulkActions();
        $this->amendBulkActionsForCouriers($bulkActionsViewModel)
            ->amendBulkActionsForUsage($bulkActionsViewModel);

        return $bulkActionsViewModel;
    }

    protected function amendBulkActionsForCouriers(BulkActionsViewModel $bulkActionsViewModel)
    {
        $courierAccountsPresent = $this->hasCourierAccounts();
        $manifestableAccountsPresent = $this->hasManifestableCourierAccounts();
        if ($courierAccountsPresent && $manifestableAccountsPresent) {
            return $this;
        }
        foreach ($bulkActionsViewModel->getActions() as $action) {
            if (!($action instanceof CourierBulkAction)) {
                continue;
            }
            if (!$courierAccountsPresent) {
                $bulkActionsViewModel->getActions()->detach($action);
                break;
            }
            foreach ($action->getSubActions() as $subAction) {
                if (!($subAction instanceof CourierManifestBulkAction)) {
                    continue;
                }
                $action->getSubActions()->detach($subAction);
                break 2;
            }
        }

        return $this;
    }

    protected function hasCourierAccounts()
    {
        try {
            $courierAccounts = $this->courierService->getShippingAccounts();
            return (count($courierAccounts) > 0);
        } catch (NotFound $e) {
            return false;
        }
    }

    protected function hasManifestableCourierAccounts()
    {
        try {
            $manifestableAccounts = $this->manifestService->getShippingAccounts();
            return (count($manifestableAccounts) > 0);
        } catch (NotFound $e) {
            return false;
        }
    }

    protected function amendBulkActionsForUsage(BulkActionsViewModel $bulkActionsViewModel)
    {
        if(!$this->getUsageService()->hasUsageBeenExceeded()) {
            return $this;
        }

        $actions = $bulkActionsViewModel->getActions();
        foreach($actions as $action) {
            $action->setEnabled(false);
        }
        return $this;
    }

    public function orderAction()
    {
        if ($this->getUsageService()->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }

        $order = $this->getOrderService()->getOrder($this->params('order'));
        $carriers = $this->getCarrierSelect();
        $view = $this->getViewModelFactory()->newInstance(
            [
                'order' => $order
            ]
        );
        $bulkActions = $this->getBulkActionsForOrder($order);
        $bulkActions->addChild(
            $this->getViewModelFactory()->newInstance()->setTemplate('orders/orders/bulk-actions/order'),
            'afterActions'
        );

        $productPaymentInfo = $this->getProductAndPaymentDetails($order);
        $labelDetails = $this->getShippingLabelDetails($order);
        $accountDetails = $this->getAccountDetails($order);
        $orderDetails = $this->getOrderDetails($order);
        $statusTemplate = $this->getStatus($order->getStatus(), $this->getOrderService()->getStatusMessageForOrder($order->getId(), $order->getStatus()));

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
        $view->setVariable('editable', $this->getOrderService()->isOrderEditable($order));

        $this->addLabelPrintButtonToView($view, $order);
        return $view;
    }

    protected function getBulkActionsForOrder(OrderEntity $order)
    {
        $bulkActions = $this->getBulkActionsService()->getOrderBulkActions($order);
        if ($this->hasCourierAccounts()) {
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

    protected function getProductAndPaymentDetails(OrderEntity $order)
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate('orders/orders/order/productPaymentInfo');


        $view->setVariable('order', $order);
        $view->setVariable('vatOu', $this->getOrderService()->getVatOrganisationUnitForOrder($order));

        if ($order->isEligibleForZeroRateVat()) {
            $recipientVatNumber = $order->getRecipientVatNumber();
            $view->setVariable('isOrderZeroRated', (isset($recipientVatNumber) && strlen($recipientVatNumber)));
            $view->setVariable('vatNumber', substr($recipientVatNumber, 2));

            $view->addChild($this->getZeroRatedCheckbox($recipientVatNumber), 'zeroRatedCheckbox');
            $view->addChild($this->getRecipientVatNumberSelectbox($order, $recipientVatNumber), 'zeroRatedSelectBox');
        }

        $view->addChild($this->getOrderService()->getOrderItemTable($order), 'productPaymentTable');

        return $view;
    }

    protected function getRecipientVatNumberSelectbox($order, $recipientVatNumber = null)
    {
        $initialValue = $order->getCalculatedShippingAddressCountryCode();
        if ($recipientVatNumber !== null && $recipientVatNumber !== '') {
            $initialValue = substr($recipientVatNumber, 0, 2);
        }
        return EUCountryNameByVATCode::getVatCodeSelectbox($this->viewModelFactory, $initialValue, 'zero-rated-vat-code-select', 'zeroRatedVatCode');
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

    protected function getShippingLabelDetails(OrderEntity $order)
    {
        $filter = (new OrderLabelFilter())
            ->setOrderId([$order->getId()]);

        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate('orders/orders/order/shippingLabelDetails');
        $view->setVariable('shippingMethod', $order->getShippingMethod());
        $view->setVariable('order', $order);

        try {
            $labels = $this->orderLabelService->fetchCollectionByFilter($filter);

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
            $view->addChild($this->getPrintLabelButton($view, $order), 'printButton');
        } catch (NotFound $e) {
            $view->addChild($this->getCarrierSelect(), 'carrierSelect');
            $view->setVariable('tracking', $order->getFirstTracking());
        }

        return $view;
    }

    protected function getPrintLabelButton($view, $order)
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

    protected function getAccountDetails(OrderEntity $order)
    {
        /** @var Account $account */
        $account = $this->accountService->fetch($order->getAccountId());
        $view = $this->getViewModelFactory()->newInstance();
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
        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate("elements/channel-large.mustache");
        $view->setVariable('channel', $account->getChannel());
        if (isset($externalData['imageUrl']) && !empty($externalData['imageUrl'])) {
            $view->setVariable('channelImgUrl', $externalData['imageUrl']);
        }

        return $view;
    }

    protected function getBuyerMessage(OrderEntity $order)
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate('orders/orders/order/buyerMessage');
        if (! $order) {
            return $view;
        }
        $view->setVariable('buyerMessage', $order->getBuyerMessage() ?: $this->translate("There is no buyer message for this order"));
        return $view;
    }

    protected function getOrderAlert(OrderEntity $order)
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate('orders/orders/order/orderAlert');
        if (! $order) {
            return $view;
        }
        $view->setVariable('order', $order);
        return $view;
    }

    protected function getAddressInformation(OrderEntity $order)
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate('orders/orders/order/addressInformation');
        $view->setVariable('order', $order);
        $view->setVariable('addressSaveUrl', 'Orders/order/address');
        $view->setVariable('editable', $this->getOrderService()->isOrderEditable($order));
        $view->setVariable('requiresSaveButton', true);
        $view->setVariable('includeAddressCopy', true);
        $view->setVariable('includeUseBillingInfo', false);
        return $view;
    }

    protected function getOrderDetails(OrderEntity $order)
    {
        $view = $this->getViewModelFactory()->newInstance();
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
        $status = $this->getViewModelFactory()->newInstance();
        $status->setTemplate("columns/status.mustache");
        $status->setVariable('status', $statusText);
        $status->setVariable('message', $messageText);
        $status->setVariable('statusClass', str_replace(' ', '-', $statusText));

        return $status;
    }

    protected function getCarrierSelect()
    {
        $order = $this->getOrderService()->getOrder($this->params('order'));
        $priorityOptions = $this->getOrderService()->getCarrierPriorityOptions();
        $carriers = $this->getOrderService()->getCarriersData();
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
        $carrierSelect = $this->getViewModelFactory()->newInstance(["options" => $options]);
        $carrierSelect->setTemplate("elements/custom-select.mustache");
        $carrierSelect->setVariable("name", "carrier");
        $carrierSelect->setVariable("id", "carrier");
        $carrierSelect->setVariable("blankOption", true);
        $carrierSelect->setVariable("searchField", true);
        $carrierSelect->setVariable("priorityOptions", $priorityOptions);
        return $carrierSelect;
    }

    protected function addLabelPrintButtonToView(ViewModel $view, OrderEntity $order)
    {
        try {
            $this->getPrintableOrderLabelForOrder($order);
        } catch (NotFound $e) {
            return;
        }
        $buttons = $this->getPrintLabelButton($view, $order);
        $view->addChild($buttons, 'printShippingLabelButton');
    }

    protected function getPrintableOrderLabelForOrder(OrderEntity $order)
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

    protected function getBatches()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate('layout/sidebar/batches');
        $view->setVariable('batches', $this->getBatchService()->getBatches());
        return $view;
    }

    protected function getTimelineBoxes(OrderEntity $order)
    {
        $timelineBoxes = $this->getViewModelFactory()->newInstance(
            $this->getTimelineService()->getTimeline($order)
        );
        $timelineBoxes->setTemplate('elements/timeline-boxes');
        return $timelineBoxes;
    }

    protected function getNotes(OrderEntity $order)
    {
        $existingNotes = $this->getOrderService()->getNamesFromOrderNotes($order->getNotes());

        usort($existingNotes, function ($a, $b) {
            return strtotime($a['timestamp']) > strtotime($b['timestamp']);
        });

        return json_encode($existingNotes);
    }

    protected function getFilterBar()
    {
        /** @var Filter $filterValues */
        if ($searchTerm = $this->params()->fromQuery('search')) {
            $filterValues = (new Filter())->setSearchTerm($searchTerm);
        } else {
            $filterValues = $this->getFilterService()->getPersistentFilter();
        }
        $filters = $this->getUIFiltersService()->getFilters(static::FILTER_TYPE, $filterValues);
        return $filters->prepare();
    }

    protected function getDetailsSidebar()
    {
        $sidebar = $this->getViewModelFactory()->newInstance();
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

    protected function getDefaultJsonData()
    {
        return new ArrayObject(
            [
                'iTotalRecords' => 0,
                'iTotalDisplayRecords' => 0,
                'sEcho' => (int) $this->params()->fromPost('sEcho'),
                'Records' => [],
                'sFilterId' => null,
            ]
        );
    }

    protected function mergeOrderDataWithJsonData(PageLimit $pageLimit, ArrayObject $json, array $orderData)
    {
        $json['Records'] = $pageLimit->getPageData($orderData['orders']);
        $json['iTotalRecords'] = $json['iTotalDisplayRecords'] = $orderData['orderTotal'];
        $json['sFilterId'] = $orderData['filterId'];
        return $this;
    }

    protected function getPageLimit()
    {
        $pageLimit = new PageLimit();

        if ($this->params()->fromPost('iDisplayLength') > 0) {
            $pageLimit
                ->setLimit($this->params()->fromPost('iDisplayLength'))
                ->setPageFromOffset($this->params()->fromPost('iDisplayStart'));
        }

        return $pageLimit;
    }

    protected function getOrderBy()
    {
        $orderBy = new OrderBy();

        $orderByIndex = $this->params()->fromPost('iSortCol_0');
        if ($orderByIndex) {
            $orderBy
                ->setColumn($this->params()->fromPost('mDataProp_' . $orderByIndex))
                ->setDirection($this->params()->fromPost('sSortDir_0', 'asc'));
        }

        return $orderBy;
    }

    public function jsonFilterAction()
    {
        $data = $this->getDefaultJsonData();
        $pageLimit = $this->getPageLimit();
        $orderBy = $this->getOrderBy();

        $filter = $this->getFilterService()->getFilter()
            ->setOrganisationUnitId($this->getOrderService()->getActiveUser()->getOuList())
            ->setPage($pageLimit->getPage())
            ->setLimit($pageLimit->getLimit())
            ->setOrderBy($orderBy->getColumn())
            ->setOrderDirection($orderBy->getDirection());

        $requestFilter = $this->params()->fromPost('filter', []);
        $this->getFilterService()->setPersistentFilter(
            new DisplayFilter(
                isset($requestFilter['more']) && is_array($requestFilter['more']) ? $requestFilter['more'] : [],
                $this->getFilterService()->getFilterFromArray($requestFilter)
            )
        );

        $requestFilter = $this->filterService->addDefaultFiltersToArray($requestFilter);
        if (!empty($requestFilter)) {
            $filter = $this->getFilterService()->mergeFilters(
                $filter,
                $this->getFilterService()->getFilterFromArray($requestFilter)
            );
        }

        // Must reformat dates *after* persisting otherwise it'll happen again when its reloaded
        if ($filter->getPurchaseDateFrom()) {
            $filter->setPurchaseDateFrom($this->dateFormatInput($filter->getPurchaseDateFrom()));
        }
        if ($filter->getPurchaseDateTo()) {
            $filter->setPurchaseDateTo($this->dateFormatInput($filter->getPurchaseDateTo()));
        }

        try {
            $orders = $this->getOrderService()->getOrders($filter);
            $this->mergeOrderDataWithJsonData(
                $pageLimit,
                $data,
                $this->getOrderService()->alterOrderTable($orders, $this->getEvent())
            );
        } catch (NotFound $exception) {
            // No Orders so ignoring
        }

        return $this->getJsonModelFactory()->newInstance($data);
    }

    public function jsonFilterIdAction()
    {
        $data = $this->getDefaultJsonData();
        $pageLimit = $this->getPageLimit();
        $orderBy = $this->getOrderBy();
        $filterId = $this->params()->fromRoute('filterId');

        $this->logDebugDump($filterId, "Filter id: ");

        try {
            $orders = $this->getOrderService()->getOrdersFromFilterId(
                $filterId,
                $pageLimit->getLimit(),
                $pageLimit->getPage(),
                $orderBy->getColumn(),
                $orderBy->getDirection()
            );

            $this->mergeOrderDataWithJsonData(
                $pageLimit,
                $data,
                $this->getOrderService()->alterOrderTable($orders, $this->getEvent())
            );
        } catch (NotFound $exception) {
            // No Orders so ignoring
        }

        return $this->getJsonModelFactory()->newInstance($data);
    }


    public function orderCountsAjaxAction()
    {
        $organisationUnitId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $entity = $this->orderCountsApi->fetch($organisationUnitId);
        $data = $entity->toArray();
        return $this->getJsonModelFactory()->newInstance($data);
    }

    public function getDeferredColumnDataAction()
    {
        $orderIds = $this->params()->fromPost('orderIds');
        $ordersById = [];
        try {
            $filter = (new OrderLabelFilter())
                ->setOrderId($orderIds);
            $labels = $this->orderLabelService->fetchCollectionByFilter($filter);

            foreach ($labels as $label) {
                $ordersById[$label->getOrderId()]['labelCreatedDate'] = $label->getCreated();
            }
        } catch (NotFound $exception) {
            // No Orders so ignoring
        }

        return $this->getJsonModelFactory()->newInstance(['newData' => $ordersById]);
    }

    public function updateColumnsAction()
    {
        $response = $this->getJsonModelFactory()->newInstance(['updated' => false]);

        $updatedColumns = $this->params()->fromPost('columns');
        if (!$updatedColumns) {
            return $response->setVariable('error', 'No columns provided');
        }

        $this->getOrderService()->updateUserPrefOrderColumns($updatedColumns);

        return $response->setVariable('updated', true);
    }

    public function updateColumnOrderAction()
    {
        $response = $this->getJsonModelFactory()->newInstance(['updated' => false]);
        $this->updateColumnPositions();
        return $response->setVariable('updated', true);
    }

    protected function updateColumnPositions()
    {
        $keyPrefix = 'mDataProp_';
        $columnPositions = [];
        $post = $this->params()->fromPost();
        foreach ($post as $key => $value) {
            if (strpos($key, $keyPrefix) === 0) {
                $columnPositions[$value] = substr($key, strlen($keyPrefix));
            }
        }

        $this->getOrderService()->updateUserPrefOrderColumnPositions($columnPositions);
    }

    public function imagesForOrdersAction()
    {
        $orderIds = $this->params()->fromPost('orders');
        $imagesForOrders = $this->orderService->getImagesForOrders($orderIds);
        return $this->getJsonModelFactory()->newInstance($imagesForOrders);
    }

    public function setRecipientVatNumberAction()
    {
        $orderId = $this->params()->fromPost('order');
        $countryCode = $this->params()->fromPost('countryCode');
        $vatNumber = $this->params()->fromPost('vatNumber');

        $order = $this->orderService->getOrder($orderId);

        $response = $this->getJsonModelFactory()->newInstance(['success' => false]);
        try {
            $currencyFormatter = $this->currencyFormat;
            $this->getOrderService()->saveRecipientVatNumberToOrder($order, $countryCode, $vatNumber);
            $response->setVariable('orderSubTotal', $currencyFormatter($order->getSubTotal(), $order->getCurrencyCode()));
            $response->setVariable('orderTotal', $currencyFormatter($order->getTotal(), $order->getCurrencyCode()));
            $response->setVariable('orderTax', $currencyFormatter($order->getTax(), $order->getCurrencyCode()));
            $response->setVariable('success', true);
        } catch(Exception $e) {
            $response->setVariable('error', $e->getMessage());
        }

        return $response;
    }

    /**
     * @return self
     */
    protected function setUsageService(UsageService $usageService)
    {
        $this->usageService = $usageService;
        return $this;
    }

    /**
     * @return UsageService
     */
    protected function getUsageService()
    {
        return $this->usageService;
    }

    /**
     * @return self
     */
    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @return self
     */
    protected function setFilterService(FilterService $filterService)
    {
        $this->filterService = $filterService;
        return $this;
    }

    /**
     * @return FilterService
     */
    protected function getFilterService()
    {
        return $this->filterService;
    }

    /**
     * @return self
     */
    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    /**
     * @return self
     */
    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return ViewModelFactory
     */
    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    /**
     * @return self
     */
    protected function setBatchService(BatchService $batchService)
    {
        $this->batchService = $batchService;
        return $this;
    }

    /**
     * @return BatchService
     */
    protected function getBatchService()
    {
        return $this->batchService;

    }

    /**
     * @return self
     */
    protected function setTimelineService(TimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
        return $this;
    }

    /**
     * @return TimelineService
     */
    protected function getTimelineService()
    {
        return $this->timelineService;
    }

    /**
     * @return self
     */
    protected function setBulkActionsService(BulkActionsService $bulkActionsService)
    {
        $this->bulkActionsService = $bulkActionsService;
        return $this;
    }

    /**
     * @return BulkActionsService
     */
    protected function getBulkActionsService()
    {
        return $this->bulkActionsService;
    }

    /**
     * @return self
     */
    protected function setUIFiltersService(UIFiltersService $uiFiltersService)
    {
        $this->uiFiltersService = $uiFiltersService;
        return $this;
    }

    /**
     * @return UIFiltersService
     */
    protected function getUIFiltersService()
    {
        return $this->uiFiltersService;
    }

    /**
     * @return self
     */
    protected function setStoredFiltersService(StoredFiltersService $storedFiltersService)
    {
        $this->storedFiltersService = $storedFiltersService;
        return $this;
    }

    /**
      @return StoredFiltersService
    */
    protected function getStoredFiltersService()
    {
        return $this->storedFiltersService;
    }

    /**
     * @return self
     */
    protected function setShippingConversionService(ShippingConversionService $shippingConversionService)
    {
        $this->shippingConversionService = $shippingConversionService;
        return $this;
    }

    /**
     * @return ShippingConversionService
     */
    protected function getShippingConversionService()
    {
        return $this->shippingConversionService;
    }

    /**
     * @return self
     */
    public function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    /**
     * @return self
     */
    public function setOrderCountsApi(OrderCountsApi $orderCountsApi)
    {
        $this->orderCountsApi = $orderCountsApi;
        return $this;
    }

    /**
     * @return self
     */
    public function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return self
     */
    protected function setOrderLabelService(OrderLabelService $orderLabelService)
    {
        $this->orderLabelService = $orderLabelService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setManifestService(ManifestService $manifestService)
    {
        $this->manifestService = $manifestService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setCourierService(CourierService $courierService)
    {
        $this->courierService = $courierService;
        return $this;
    }
}
