<?php
namespace Products\Stock\Settings;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Product\Client\Service as ProductService;
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
    /** @var ProductService */
    protected $productService;

    protected $stockModeOptions;

    public function __construct(
        UserOUService $userOUService,
        ProductSettingsService $productSettingsService,
        ProductService $productService
    ) {
        $this->setUserOUService($userOUService)
            ->setProductSettingsService($productSettingsService)
            ->setProductService($productService);
    }

    public function getProductStockModeOptions(Product $product)
    {
        $options = $this->getStockModeOptions();
        $productOption = ($product->getStockMode() ?: 'null');
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
        // Can't use any value that equates to false (e.g. '') as then custom-select will replace it with the title
        $defaultOption = ['value' => 'null', 'title' => 'Default (' . $defaultStockModeTitle . ')'];
        array_unshift($options, $defaultOption);
        $this->stockModeOptions = $options;
        return $this->stockModeOptions;
    }

    public function saveProductStockMode($productId, $stockMode)
    {
        if ($stockMode !== null && !StockMode::isValid($stockMode)) {
            throw new \InvalidArgumentException('"' . $stockMode . '" is not a valid stock mode option');
        }
        $product = $this->productService->fetch($productId);
        $product->setStockMode($stockMode);
        try {
            $this->productService->save($product);
        } catch (NotModified $e) {
            // No-op
        }
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

    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }
}
