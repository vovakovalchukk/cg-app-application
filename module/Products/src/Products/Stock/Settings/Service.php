<?php
namespace Products\Stock\Settings;

use CG\Product\Entity as Product;
use CG\Product\StockMode;
use CG\Settings\Product\Service as ProductSettingsService;
use CG\User\OrganisationUnit\Service as UserOUService;

class Service
{
    /** @var UserOUService */
    protected $userOUService;
    /** @var ProductSettingsService */
    protected $productSettingsService;

    protected $stockModeOptions;

    public function __construct(
        UserOUService $userOUService,
        ProductSettingsService $productSettingsService
    ) {
        $this->setUserOUService($userOUService)
            ->setProductSettingsService($productSettingsService);
    }

    public function getProductStockModeOptions(Product $product)
    {
        $options = $this->getStockModeOptions();
        $productOption = ($product->getStockMode() ?: '');
        foreach ($options as &$option) {
            if ($option['value'] == $productOption) {
                $option['selected'] = true;
                break;
            }
        }
        return $options;
    }

    protected function getStockModeOptions()
    {
        if ($this->stockModeOptions) {
            return $this->stockModeOptions;
        }
        $options = StockMode::getStockModesAsSelectOptions();
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $productSettings = $this->productSettingsService->fetch($rootOu->getId());
        $defaultStockMode = ($productSettings->getDefaultStockMode() ?: StockMode::LIST_ALL);
        $defaultStockModeTitle = '';
        foreach ($options as $option) {
            if ($option['value'] == $defaultStockMode) {
                $defaultStockModeTitle = $option['title'];
                break;
            }
        }
        array_unshift($options, ['value' => '', 'title' => 'Default (' . $defaultStockModeTitle . ')']);
        $this->stockModeOptions = $options;
        return $this->stockModeOptions;
    }

    protected function setUserOUService(UserOUService $userOUService)
    {
        $this->userOUService = $userOUService;
        return $this;
    }

    protected function setProductSettingsService(ProductSettingsService $productSettingsService)
    {
        $this->productSettingsService = $productSettingsService;
        return $this;
    }
}
