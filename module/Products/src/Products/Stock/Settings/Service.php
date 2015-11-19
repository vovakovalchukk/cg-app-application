<?php
namespace Products\Stock\Settings;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Product\Client\Service as ProductService;
use CG\Product\Collection as ProductCollection;
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
            $stockMode = $this->getStockModeDefault();
        }
        if (!$stockMode) {
            return null;
        }
        return StockMode::getStockModeDescription($stockMode);
    }

    public function getStockModeDefault()
    {
        $productSettings = $this->getProductSettings();
        return $productSettings->getDefaultStockMode();
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
            if ($stockMode == null || $stockMode == StockMode::LIST_ALL) {
                $product->setStockLevel(null);
            }
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

    protected function saveProductStockModeForVariations(Product $product, $stockMode)
    {
        if (!$product->isParent()) {
            return;
        }
        // Fetch the variations rather than getting them straight off the parent otherwise we'll be missing their eTags
        $variations = $this->getVariationsForParentId($product->getId());
        foreach ($variations as $variation) {
            $variation->setStockMode($stockMode);
            if ($stockMode == null || $stockMode == StockMode::LIST_ALL) {
                $variation->setStockLevel(null);
            }
            $this->productService->save($variation);
        }
        $product->setVariations($variations);
    }

    protected function getVariationsForParentId($parentId)
    {
        $filter = (new ProductFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setParentProductId([$parentId]);
        return $this->productService->fetchCollectionByFilter($filter);
    }

    /**
     * @return string
     */
    public function saveProductStockLevel($productId, $stockLevel, $eTag)
    {
        try {
            $affectedProducts = null;
            $product = $this->productService->fetch($productId);
            $product->setStockLevel($stockLevel);
            if ($eTag) {
                $product->setStoredEtag($eTag);
            }
            $this->productService->save($product);
            $affectedProducts = $this->saveProductStockLevelForSiblingVariations($product, $stockLevel);
        } catch (NotModified $e) {
            // No-op
        }
        if (!$affectedProducts) {
            $affectedProducts = new ProductCollection(Product::class, __FUNCTION__);
            $affectedProducts->attach($product);
        }
        return $affectedProducts;
    }

    protected function saveProductStockLevelForSiblingVariations(Product $variation, $stockLevel)
    {
        if (!$variation->isVariation()) {
            return null;
        }
        $siblings = $this->getVariationsForParentId($variation->getParentProductId());
        $allZero = $this->checkAllSiblingsHaveZeroStockLevel($variation, $siblings);
        if (!$allZero) {
            return null;
        }
        foreach ($siblings as $sibling) {
            if ($sibling->getId() == $variation->getId()) {
                continue;
            }
            $sibling->setStockLevel($stockLevel);
            // Not setting the eTag as we don't have it. As the initial variation saved OK we'll assume it's fine to save the rest
            $this->productService->save($sibling);
        }
        return $siblings;
    }

    protected function checkAllSiblingsHaveZeroStockLevel(Product $variation, ProductCollection $siblings)
    {
        $allZero = true;
        foreach ($siblings as $sibling) {
            if ($sibling->getId() == $variation->getId()) {
                continue;
            }
            if ($sibling->getStockLevel() != 0) {
                $allZero = false;
                break;
            }
        }
        return $allZero;
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
