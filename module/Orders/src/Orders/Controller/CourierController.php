<?php
namespace Orders\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Collection as AccountCollection;
use CG_UI\View\DataTable;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Zend\Stdlib\Http\FileResponse;
use Orders\Module;
use Orders\Courier\Label\PrintService as LabelPrintService;
use Orders\Courier\Manifest\Service as ManifestService;
use Orders\Courier\SpecificsPage as SpecificsPageService;
use Orders\Courier\Service;
use Orders\Courier\SpecificsAjax as SpecificsAjaxService;
use Orders\Courier\ShippingAccountsService;
use Orders\Order\BulkActions\OrdersToOperateOn;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter;

class CourierController extends AbstractActionController
{
    const ROUTE = 'Courier';
    const ROUTE_URI = '/courier';
    const ROUTE_REVIEW = 'Review';
    const ROUTE_REVIEW_URI = '/review';
    const ROUTE_SPECIFICS = 'Specifics';
    const ROUTE_SPECIFICS_URI = '/specifics[/:account]';
    const ROUTE_LABEL = 'Label';
    const ROUTE_LABEL_URI = '/label';
    const ROUTE_LABEL_PRINT = 'Print';
    const ROUTE_LABEL_PRINT_URI = '/print';
    const ROUTE_MANIFEST_PRINT = 'Print';
    const ROUTE_MANIFEST_PRINT_URI = '/:manifestId';

    const LABEL_MIME_TYPE = 'application/pdf';
    const MANIFEST_MIME_TYPE = 'application/pdf';

    protected $viewModelFactory;
    protected $reviewTable;
    protected $specificsTable;
    /** @var Service */
    protected $service;
    /** @var SpecificsPageService */
    protected $specificsPageService;
    /** @var LabelPrintService */
    protected $labelPrintService;
    /** @var ManifestService */
    protected $manifestService;
    /** @var OrdersToOperateOn */
    protected $ordersToOperatorOn;
    /** @var ShippingAccountsService */
    protected $shippingAccountsService;
    /** @var OrderService */
    protected $orderService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        DataTable $reviewTable,
        DataTable $specificsTable,
        Service $service,
        SpecificsPageService $specificsPageService,
        LabelPrintService $labelPrintService,
        ManifestService $manifestService,
        OrdersToOperateOn $ordersToOperatorOn,
        ShippingAccountsService $shippingAccountsService,
        OrderService $orderService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->reviewTable = $reviewTable;
        $this->specificsTable = $specificsTable;
        $this->service = $service;
        $this->specificsPageService = $specificsPageService;
        $this->labelPrintService = $labelPrintService;
        $this->manifestService = $manifestService;
        $this->ordersToOperatorOn = $ordersToOperatorOn;
        $this->shippingAccountsService = $shippingAccountsService;
        $this->orderService = $orderService;
    }

    public function indexAction()
    {
        $requestParams = ['action' => 'review'];

        $orders = $this->getOrdersFromInput();
        $shippingAccounts = $this->service->getShippingAccountsForOrders($orders);
        if (count($shippingAccounts) == 1) {
            $shippingAccount = array_pop($shippingAccounts);
            $this->setSpecificsPostParams($orders->getIds(), $shippingAccount['id']);

            $requestParams['action'] = 'specifics';
            $requestParams['account'] = $shippingAccount['id'];
        }
        return $this->forward()->dispatch(CourierController::class, $requestParams);
    }

    /**
     * @return ViewModel
     */
    public function reviewAction()
    {
        $orders = $this->getOrdersFromInput();
        $orderIds = $orders->getIds();
        $view = $this->viewModelFactory->newInstance();
        $this->prepReviewTable();

        $view->setVariable('goBackUrl', $this->calculateGoBackUrl());
        $view->setVariable('orderIds', $orderIds);
        $view->setVariable('specificsUrl', $this->url()->fromRoute(Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_SPECIFICS));
        $view->addChild($this->reviewTable, 'reviewTable');
        $view->addChild($this->getReviewContinueButton(), 'continueButton');
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('isSidebarPresent', false);
        $view->setVariable('subHeaderHide', true);

        return $view;
    }

    protected function calculateGoBackUrl()
    {
        $goBackUrlDefault = '/orders';
        $goBackUrl = $this->params()->fromPost('referrer', $goBackUrlDefault);
        return $goBackUrl;
    }

    /**
     * @param array $orderIds
     * @param int $courierAccountId
     */
    protected function setSpecificsPostParams(array $orderIds, $courierAccountId)
    {
        $this->getRequest()->getPost()->set('order', $orderIds);

        $filter = (new Filter())
            ->setLimit('all')
            ->setOrderIds($orderIds);
        $orders = $this->orderService->fetchCollectionByFilter($filter);

        foreach($orders as $order) {
            $this->getRequest()->getPost()->set('courier_'.$order->getId(), $courierAccountId);
        }
    }

    protected function getOrdersFromInput()
    {
        $input = $this->params()->fromPost();
        $ordersToOperatorOn = $this->ordersToOperatorOn;
        return $ordersToOperatorOn($input);
    }

    protected function prepReviewTable()
    {
        $settings = $this->reviewTable->getVariable('settings');
        $settings->setSource(
            $this->url()->fromRoute(
                Module::ROUTE . '/' . static::ROUTE . '/' . static::ROUTE_REVIEW . '/' . CourierJsonController::ROUTE_REVIEW_LIST
            )
        );
        $settings->setTemplateUrlMap($this->mustacheTemplateMap('courierReview'));
    }

    protected function getReviewContinueButton()
    {
        $view = $this->viewModelFactory->newInstance([
            'buttons' => [
                'value' => 'Continue',
                'id' => 'continue-button',
                'disabled' => false,
            ]
        ]);
        $view->setTemplate('elements/buttons.mustache');
        return $view;
    }

    /**
     * @return ViewModel
     */
    public function specificsAction()
    {
        $orderIds = $this->params()->fromPost('order', []);
        $selectedCourierId = $this->params()->fromRoute('account');
        $courierIds = [];
        $courierOrders = [];
        $orderCouriers = [];
        $orderServices = [];
        foreach ($orderIds as $orderId) {
            $courierId = $this->params()->fromPost('courier_'.$orderId);
            $serviceId = $this->params()->fromPost('service_'.$orderId);
            if (!$courierId) {
                throw new \InvalidArgumentException('Order '.$orderId.' provided but no matching courier or service option was found');
            }
            $courierIds[] = $courierId;
            if (!isset($courierOrders[$courierId])) {
                $courierOrders[$courierId] = [];
            }
            $courierOrders[$courierId][] = $orderId;
            $orderCouriers[$orderId] = $courierId;
            $orderServices[$orderId] = $serviceId;
        }

        $courierAccounts = $this->specificsPageService->fetchAccountsById($courierIds);
        if ($selectedCourierId) {
            $selectedCourier = $courierAccounts->getById($selectedCourierId);
        } else {
            $courierAccounts->rewind();
            $selectedCourier = $courierAccounts->current();
            $selectedCourierId = $selectedCourier->getId();
        }
        $this->prepSpecificsTable($selectedCourierId);
        $navLinks = $this->getSidebarNavLinksForSelectedAccounts($courierAccounts);

        $this->specificsPageService->alterSpecificsTableForSelectedCourier($this->specificsTable, $selectedCourier);

        $view = $this->viewModelFactory->newInstance();
        $view->setVariable('orderIds', $orderIds)
            ->setVariable('goBackUrl', $this->calculateGoBackUrl())
            ->setVariable('courierOrderIds', $courierOrders[$selectedCourierId])
            ->setVariable('orderCouriers', $orderCouriers)
            ->setVariable('orderServices', $orderServices)
            ->setVariable('navLinks', $navLinks)
            ->setVariable('selectedCourier', $selectedCourier)
            ->addChild($this->getSpecificsBulkActionsButtons($courierAccounts, $selectedCourier), 'bulkActionsButtons')
            ->addChild($this->specificsTable, 'specificsTable')
            ->addChild($this->getSpecificsActionsButtons($selectedCourier), 'actionsButtons')
            ->addChild($this->getSpecificsParcelsElement(), 'parcelsElement')
            ->addChild($this->getSpecificsCollectionDateElement(), 'collectionDateElement')
            ->addChild($this->getItemParcelAssignmentButton(), 'itemParcelAssignmentButton')
            ->setVariable('isHeaderBarVisible', false)
            ->setVariable('isSidebarPresent', (count($courierOrders) > 1))
            ->setVariable('subHeaderHide', true);

        return $view;
    }

    protected function prepSpecificsTable($selectedCourierId)
    {
        $settings = $this->specificsTable->getVariable('settings');
        $settings->setSource(
            $this->url()->fromRoute(
                Module::ROUTE . '/' . static::ROUTE . '/' . static::ROUTE_SPECIFICS . '/' . CourierJsonController::ROUTE_SPECIFICS_LIST,
                ['account' => $selectedCourierId]
            )
        );
        $settings->setTemplateUrlMap($this->mustacheTemplateMap('courierSpecifics'));
    }

    public function getSidebarNavLinksForSelectedAccounts(AccountCollection $accounts)
    {
        $route = Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_SPECIFICS;
        $nav = [];
        foreach ($accounts as $account) {
            list($url, $linkText) = $this->getSidebarNavLinkForSelectedAccount($account);
            $nav[$url] = $linkText;
        }
        return $nav;
    }

    protected function getSidebarNavLinkForSelectedAccount(Account $account)
    {
        $route = Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_SPECIFICS;
        $url = $this->url()->fromRoute($route, ['account' => $account->getId()]);
        return [$url, $this->shippingAccountsService->getDisplayNameForAccount($account)];
    }

    protected function getSpecificsBulkActionsButtons(AccountCollection $accounts, Account $selectedAccount)
    {
        $viewConfig = [
            'buttons' => [
                [
                    'value' => $this->specificsPageService->getCreateAllActionDescription($selectedAccount),
                    'id' => 'create-all-labels-button',
                    'class' => 'courier-create-all-labels-button courier-status-all-labels-button',
                    'disabled' => false,
                ],
                [
                    'value' => $this->specificsPageService->getExportAllActionDescription($selectedAccount),
                    'id' => 'export-all-labels-button',
                    'class' => 'courier-export-all-labels-button courier-status-all-labels-button',
                    'disabled' => false,
                ],
                [
                    'value' => $this->specificsPageService->getPrintAllActionDescription($selectedAccount),
                    'id' => 'print-all-labels-button',
                    'class' => 'courier-print-all-labels-button courier-status-all-labels-button',
                    'disabled' => false,
                ],
                [
                    'value' => $this->specificsPageService->getCancelAllActionDescription($selectedAccount),
                    'id' => 'cancel-all-labels-button',
                    'class' => 'courier-cancel-all-labels-button courier-status-all-labels-button',
                    'disabled' => false,
                ],
                [
                    'value' => $this->specificsPageService->getDispatchAllActionDescription($selectedAccount),
                    'id' => 'dispatch-all-labels-button',
                    'class' => 'courier-dispatch-all-labels-button courier-status-all-labels-button',
                    'disabled' => false,
                ],
            ]
        ];
        if (count($accounts) > 1 && $nextCourierButtonConfig = $this->getNextCourierButtonConfig($accounts, $selectedAccount)) {
            array_unshift($viewConfig['buttons'], $nextCourierButtonConfig);
        }

        $view = $this->viewModelFactory->newInstance($viewConfig);
        $view->setTemplate('elements/buttons.mustache');
        return $view;
    }

    protected function getNextCourierButtonConfig(AccountCollection $accounts, Account $selectedAccount)
    {
        $nextCourier = null;
        foreach ($accounts as $account) {
            if ($account->getId() == $selectedAccount->getId()) {
                $accounts->next();
                $nextCourier = $accounts->current();
                break;
            }
        }
        if (!$nextCourier) {
            return null;
        }
        list($nextCourierUrl, ) = $this->getSidebarNavLinkForSelectedAccount($nextCourier);
        return [
            'value' => 'Next Courier',
            'id' => 'next-courier-button',
            'class' => 'courier-next-courier-button',
            'disabled' => false,
            'action' => $nextCourierUrl,
        ];
    }

    protected function getSpecificsActionsButtons(Account $selectedAccount)
    {
        $view = $this->viewModelFactory->newInstance([
            'buttons' => [
                [
                    'value' => $this->specificsPageService->getCreateActionDescription($selectedAccount),
                    'id' => 'create-label-button',
                    'class' => 'courier-create-label-button',
                    'disabled' => false,
                ],
                [
                    'value' => $this->specificsPageService->getExportActionDescription($selectedAccount),
                    'id' => 'export-label-button',
                    'class' => 'courier-export-label-button',
                    'disabled' => false,
                ],
                [
                    'value' => $this->specificsPageService->getPrintActionDescription($selectedAccount),
                    'id' => 'print-label-button',
                    'class' => 'courier-print-label-button',
                    'disabled' => false,
                ],
                [
                    'value' => $this->specificsPageService->getCancelActionDescription($selectedAccount),
                    'id' => 'cancel-label-button',
                    'class' => 'courier-cancel-label-button',
                    'disabled' => false,
                ],
                [
                    'value' => $this->specificsPageService->getDispatchActionDescription($selectedAccount),
                    'id' => 'dispatch-label-button',
                    'class' => 'courier-dispatch-label-button',
                    'disabled' => false,
                ],
            ]
        ]);
        $view->setTemplate('elements/buttons.mustache');
        return $view;
    }

    protected function getSpecificsParcelsElement()
    {
        $view = $this->viewModelFactory->newInstance([
            'type' => 'number',
            'value' => 1,
            'id' => 'courier-parcels-input',
            'class' => 'courier-parcels-input number required',
            'min' => SpecificsAjaxService::MIN_PARCELS,
            'max' => SpecificsAjaxService::MAX_PARCELS,
        ]);
        $view->setTemplate('elements/inline-text.mustache');
        return $view;
    }

    protected function getSpecificsCollectionDateElement()
    {
        $view = $this->viewModelFactory->newInstance([
            'value' => date('Y-m-d'),
            'displayValue' => date('d/m/Y'),
            'id' => 'courier-order-collectionDate',
            'placeholder' => 'DD/MM/YYYY',
            'class' => 'courier-order-collectionDate required',
        ]);
        $view->setTemplate('elements/date.mustache');
        return $view;
    }

    protected function getItemParcelAssignmentButton()
    {
        $view = $this->viewModelFactory->newInstance([
            'buttons' => [
                [
                    'value' => 'Assign',
                    'id' => 'courier-itemParcelAssignment-button',
                    'class' => 'courier-itemParcelAssignment-button',
                    'disabled' => false,
                ]
            ]
        ]);
        $view->setTemplate('elements/buttons.mustache');
        return $view;
    }

    public function printLabelAction()
    {
        $orderIds = $this->params()->fromPost('order', []);
        $pdfData = $this->labelPrintService->getPdfLabelDataForOrders($orderIds);
        return new FileResponse(static::LABEL_MIME_TYPE, date('Y-m-d hi').' Labels.pdf', $pdfData);
    }

    public function printManifestAction()
    {
        $manifestId = $this->params()->fromRoute('manifestId');
        $pdfData = $this->manifestService->getManifestPdfForAccountManifest($manifestId);
        return new FileResponse(static::MANIFEST_MIME_TYPE, date('Y-m-d hi').' Manifest.pdf', $pdfData);
    }
}
