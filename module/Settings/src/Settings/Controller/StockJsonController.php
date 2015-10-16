<?php
namespace Settings\Controller;

use CG\Product\StockMode;
use CG\Settings\Product\Service as ProductSettingsService;
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

    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ProductSettingsService */
    protected $productSettingsService;
    /** @var UserOUService */
    protected $userOUService;
    /** @var Service */
    protected $service;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ProductSettingsService $productSettingsService,
        UserOUService $userOUService,
        Service $service
    ) {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setProductSettingsService($productSettingsService)
            ->setUserOUService($userOUService)
            ->setService($service);
    }

    public function saveAction()
    {
        $defaultStockMode = $this->params()->fromPost('defaultStockMode', StockMode::LIST_ALL);
        $defaultStockLevel = $this->params()->fromPost('defaultStockLevel', null);
        if ($defaultStockMode != StockMode::LIST_ALL && (!is_numeric($defaultStockLevel) || (int)$defaultStockLevel < 0)) {
            throw new \InvalidArgumentException('Default stock level must be a number >= 0');
        }
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $productSettings = $this->productSettingsService->fetch($rootOu->getId());
        $productSettings->setDefaultStockMode($defaultStockMode)
            ->setDefaultStockLevel($defaultStockLevel);
        $this->productSettingsService->save($productSettings);

        return $this->jsonModelFactory->newInstance(['valid' => true, 'status' => 'Settings saved successfully']);
    }

    public function accountsListAction()
    {
        $data = $this->getDefaultJsonData();
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $accountsData = $this->service->getAccountListData($rootOu);
        $data['iTotalRecords'] = $data['iTotalDisplayRecords'] = count($accountsData);
        $data['Records'] = $accountsData;

        return $this->jsonModelFactory->newInstance($data);
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

    protected function setProductSettingsService(ProductSettingsService $productSettingsService)
    {
        $this->productSettingsService = $productSettingsService;
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
