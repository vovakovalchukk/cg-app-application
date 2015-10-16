<?php
namespace Settings\Controller;

use CG\Product\StockMode;
use CG\Settings\Product\Service as ProductSettingsService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\User\OrganisationUnit\Service as UserOUService;
use Zend\Mvc\Controller\AbstractActionController;

class StockJsonController extends AbstractActionController
{
    const ROUTE_SAVE = 'Save';
    const ROUTE_SAVE_URI = '/save';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var ProductSettingsService */
    protected $productSettingsService;
    /** @var UserOUService */
    protected $userOUService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ProductSettingsService $productSettingsService,
        UserOUService $userOUService
    ) {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setProductSettingsService($productSettingsService)
            ->setUserOUService($userOUService);
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
}
