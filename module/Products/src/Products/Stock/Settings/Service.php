<?php
namespace Products\Stock\Settings;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Product\Client\Service as ProductService;
use CG\Product\Entity as Product;
use CG\Product\Filter as ProductFilter;
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

    protected $productSettings;
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

    /**
     * @return array
     */
    public function getStockModeOptionsForProduct(Product $product)
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
        $productSettings = $this->getProductSettings();
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

    protected function getProductSettings()
    {
        if ($this->productSettings) {
            return $this->productSettings;
        }
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $this->productSettings = $this->productSettingsService->fetch($rootOu->getId());
        return $this->productSettings;
    }

    /**
     * @return string
     */
    public function getStockModeDecriptionForProduct(Product $product)
    {
        if ($product->getStockMode()) {
            $stockMode = $product->getStockMode();
        } else {
            $productSettings = $this->getProductSettings();
            $stockMode = $productSettings->getDefaultStockMode();
        }
        if (!$stockMode) {
            return null;
        }
        return StockMode::getStockModeDescription($stockMode);
    }

    /**
     * @return int|null Null returned if stockLevel is not applicable to this Product
     */
    public function getStockLevelForProduct(Product $product)
    {
        if ($product->getStockMode() != null && $product->getStockMode() != StockMode::LIST_ALL) {
            return ($product->getStockLevel() ?: 0);
        }
        if ($product->getStockMode() == StockMode::LIST_ALL) {
            return null;
        }
        $productSettings = $this->getProductSettings();
        if ($productSettings->getDefaultStockMode() != null && $productSettings->getDefaultStockMode() != StockMode::LIST_ALL) {
            return ($productSettings->getDefaultStockLevel() ?: 0);
        }
        return null;
    }

    /**
     * @return Product
     */
    public function saveProductStockMode($productId, $stockMode, $eTag = null)
    {
        if ($stockMode !== null && !StockMode::isValid($stockMode)) {
            throw new \InvalidArgumentException('"' . $stockMode . '" is not a valid stock mode option');
        }
        try {
            $product = $this->productService->fetch($productId);
            $product->setStockMode($stockMode);
            if ($eTag) {
                $product->setStoredEtag($eTag);
            }
            $this->productService->save($product);
            $this->saveProductStockModeForVariations($product, $stockMode);
        } catch (NotModified $e) {
            // No-op
        }
        return $product;
    }

    protected function saveProductStockModeForVariations($product, $stockMode)
    {
        if (!$product->isParent()) {
            return;
        }
        // Fetch the variations rather than getting them straight off the parent otherwise we'll be missing their eTags
        $filter = (new ProductFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setId($product->getVariationIds());
        $variations = $this->productService->fetchCollectionByFilter($filter);
        foreach ($variations as $variation) {
            $variation->setStockMode($stockMode);
            $this->productService->save($variation);
        }
    }

    /**
     * @return string
     */
    public function saveProductStockLevel($productId, $stockLevel, $eTag)
    {
        try {
            $product = $this->productService->fetch($productId);
            $product->setStockLevel($stockLevel);
            if ($eTag) {
                $product->setStoredEtag($eTag);
            }
            $this->productService->save($product);
        } catch (NotModified $e) {
            // No-op
        }
        return $product->getStoredEtag();
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
