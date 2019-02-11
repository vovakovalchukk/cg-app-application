<?php
namespace Settings\Controller;

use CG\Product\StockMode;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG\User\OrganisationUnit\Service as UserOUService;
use Settings\Stock\Service;
use Zend\Mvc\Controller\AbstractActionController;

class StockJsonController extends AbstractActionController
{
    const ROUTE_SAVE = 'Save';
    const ROUTE_SAVE_URI = '/save';
    const ROUTE_ACCOUNTS = 'Accounts';
    const ROUTE_ACCOUNTS_URI = '/accounts';
    const ROUTE_ACCOUNTS_SAVE = 'Save';
    const ROUTE_ACCOUNTS_SAVE_URI = '/save';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var UserOUService */
    protected $userOUService;
    /** @var Service */
    protected $service;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        UserOUService $userOUService,
        Service $service
    ) {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setUserOUService($userOUService)
            ->setService($service);
    }

    public function saveAction()
    {
        $defaultStockMode = $this->params()->fromPost('defaultStockMode', StockMode::LIST_ALL);
        $defaultStockLevel = $this->params()->fromPost('defaultStockLevel', null);

        $lowStockThresholdOn = $this->params()->fromPost('low-stock-threshold-toggle', null) === 'on';
        $lowStockThresholdValue = $this->params()->fromPost('low-stock-threshold-value', null);

        if ($defaultStockMode != StockMode::LIST_ALL && (!is_numeric($defaultStockLevel) || (int)$defaultStockLevel < 0)) {
            throw new \InvalidArgumentException('Default stock level must be a number >= 0');
        }

        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $ouList = $this->userOUService->getAncestorOrganisationUnitIdsByActiveUser();

        $includePurchaseOrders = filter_var($this->params()->fromPost('includePurchaseOrdersInAvailable', false), FILTER_VALIDATE_BOOLEAN);

        $this->service->saveDefaults(
            $rootOu,
            $ouList,
            $defaultStockMode,
            $defaultStockLevel,
            $includePurchaseOrders,
            $lowStockThresholdOn,
            $lowStockThresholdValue
        );

        return $this->jsonModelFactory->newInstance(['valid' => true, 'status' => 'Settings saved successfully']);
    }

    public function accountsListAction()
    {
        $data = $this->getDefaultJsonData();
        $ouList = $this->userOUService->getAncestorOrganisationUnitIdsByActiveUser();
        $accountsData = $this->service->getAccountListData($ouList);
        $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = count($accountsData);
        $data['Records'] = $accountsData;

        return $this->jsonModelFactory->newInstance($data);
    }

    public function accountsSaveAction()
    {
        $accountsSettings = $this->params()->fromPost('account', []);
        $this->service->saveAccountsStockSettings($accountsSettings);
        return $this->jsonModelFactory->newInstance(['valid' => true, 'status' => 'Settings saved successfully']);
    }

    protected function getDefaultJsonData()
    {
        return [
            'iTotalRecords' => 0,
            'iTotalDisplayRecords' => 0,
            'sEcho' => (int) $this->params()->fromPost('sEcho'),
            'Records' => [],
        ];
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function setUserOUService(UserOUService $userOUService)
    {
        $this->userOUService = $userOUService;
        return $this;
    }

    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }
}
