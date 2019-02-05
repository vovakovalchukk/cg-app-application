<?php
namespace Products\Stock\Settings;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Product\Client\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as Product;
use CG\Product\Filter as ProductFilter;
use CG\Settings\Product\Entity as ProductSettings;
use CG\Settings\Product\Service as ProductSettingsService;
use CG\Stock\Entity as Stock;
use CG\Stock\Mode as StockMode;
use CG\Stock\StorageInterface as StockStorage;
use CG\User\OrganisationUnit\Service as UserOUService;

class Service
{
    /** @var UserOUService */
    protected $userOUService;
    /** @var ProductSettingsService */
    protected $productSettingsService;
    /** @var ProductService */
    protected $productService;
    /** @var StockStorage $stockStorage */
    protected $stockStorage;

    /** @var ProductSettings $productSettings */
    protected $productSettings;
    /** @var array $stockModeOptions */
    protected $stockModeOptions;
    /** @var array */
    protected $incPOStockInAvailableOptions;

    public function __construct(
        UserOUService $userOUService,
        ProductSettingsService $productSettingsService,
        ProductService $productService,
        StockStorage $stockStorage
    ) {
        $this
            ->setUserOUService($userOUService)
            ->setProductSettingsService($productSettingsService)
            ->setProductService($productService)
            ->setStockStorage($stockStorage);
    }

    /**
     * @return array
     */
    public function getStockModeOptionsForStock(Stock $stock = null)
    {
        $stockMode = $stock ? $stock->getStockMode() : null;
        return $this->getStockModeOptions($stockMode ?: 'null');
    }

    /**
     * @return array
     */
    public function getStockModeOptionsForProduct(Product $product)
    {
        return $this->getStockModeOptionsForStock(
            $this->getStockFromProduct($product)
        );
    }

    public function getStockModeOptions($stockMode = null): array
    {
        $options = $this->buildStockModeOptions();
        foreach ($options as &$option) {
            if ($option['value'] == $stockMode) {
                $option['selected'] = true;
                break;
            }
        }
        return $options;
    }

    protected function buildStockModeOptions()
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

    /**
     * @return ProductSettings
     */
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
    public function getStockModeDecriptionForStock(Stock $stock = null)
    {
        return $this->getStockModeDecription($stock ? $stock->getStockMode() : null);
    }

    /**
     * @return string
     */
    public function getStockModeDecriptionForProduct(Product $product)
    {
        return $this->getStockModeDecriptionForStock(
            $this->getStockFromProduct($product)
        );
    }

    protected function getStockModeDecription($stockMode = null)
    {
        $stockMode = $stockMode ?: null;
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

    public function getStockLevelDefault()
    {
        $productSettings = $this->getProductSettings();
        return $productSettings->getDefaultStockLevel();
    }

    /**
     * @return int|null Null returned if stockLevel is not applicable to this Product
     */
    public function getStockLevelForStock(Stock $stock = null)
    {
        return $this->getStockLevel(
            $stock ? $stock->getStockMode() : null,
            $stock ? $stock->getStockLevel() : null
        );
    }

    /**
     * @return int|null Null returned if stockLevel is not applicable to this Product
     */
    public function getStockLevelForProduct(Product $product)
    {
        return $this->getStockLevelForStock(
            $this->getStockFromProduct($product)
        );
    }

    protected function getStockLevel($stockMode = null, $stockLevel = null)
    {
        if (is_null($stockMode)) {
            $productSettings = $this->getProductSettings();
            $stockMode = $productSettings->getDefaultStockMode();
            $stockLevel = $productSettings->getDefaultStockLevel();
        }

        if (!is_null($stockMode) && $stockMode != StockMode::LIST_ALL) {
            return ($stockLevel ?: 0);
        }

        return null;
    }

    protected function saveStockMode(Stock $stock, $stockMode = null, $eTag = null)
    {
        if ($stockMode !== null && !StockMode::isValid($stockMode)) {
            throw new \InvalidArgumentException('"' . $stockMode . '" is not a valid stock mode option');
        }

        try {
            $stock->setStockMode($stockMode);
            if (is_null($stockMode) || $stockMode == StockMode::LIST_ALL) {
                $stock->setStockLevel(null);
            }
            if (!is_null($eTag)) {
                $stock->setStoredETag($eTag);
            }
            $this->stockStorage->save($stock);
        } catch (NotModified $exception) {
            // No-op
        }
    }

    /**
     * @return Stock
     */
    public function saveStockStockMode($stockId, $stockMode, $eTag = null)
    {
        $stock = $this->stockStorage->fetch($stockId);
        $this->saveStockMode($stock, $stockMode, $eTag);
        return $stock;
    }

    /**
     * @return array
     */
    public function saveProductStockMode($productId, $stockMode)
    {
        /** @var Product $product */
        $product = $this->productService->fetch($productId);
        if ($product->isParent()) {
            $variations = $product->getVariations();
        } else {
            $variations = [$product];
        }

        $skuMap = [];

        /** @var Product $variation */
        foreach ($variations as $variation) {
            $stock = $this->saveStockStockMode($variation->getStock()->getId(), $stockMode);
            $skuMap[$stock->getSku()] = [
                'mode' => $stock->getStockMode(),
                'level' => $stock->getStockLevel(),
            ];
        }

        return $skuMap;
    }

    protected function saveStockLevel(Stock $stock, $stockLevel = null, $eTag = null)
    {
        try {
            $stock->setStockLevel($stockLevel ?: 0);
            if (!is_null($eTag)) {
                $stock->setStoredETag($eTag);
            }
            $this->stockStorage->save($stock);
        } catch (NotModified $exception) {
            // No-op
        }
    }

    /**
     * @return Stock
     */
    public function saveStockStockLevel($stockId, $stockLevel, $eTag = null)
    {
        $stock = $this->stockStorage->fetch($stockId);
        $this->saveStockLevel($stock, $stockLevel, $eTag);
        return $stock;
    }

    /**
     * @return array
     */
    public function saveProductStockLevel($productId, $stockLevel)
    {
        /** @var Product $product */
        $product = $this->productService->fetch($productId);
        if ($product->isParent()) {
            $variations = $product->getVariations();
        } else {
            $variations = [$product];
        }

        $skuMap = [];

        /** @var Product $variation */
        foreach ($variations as $variation) {
            $stock = $this->saveStockStockLevel($variation->getStock()->getId(), $stockLevel);
            $skuMap[$stock->getSku()] = [
                'mode' => $stock->getStockMode(),
                'level' => $stock->getStockLevel(),
            ];
        }

        return $skuMap;
    }

    /**
     * @return Stock
     */
    protected function getStockFromProduct(Product $product)
    {
        if (!$product->isParent()) {
            return $product->getStock();
        }

        /** @var ProductCollection $variations */
        $variations = $product->getVariations();
        if ($variations->count() == 0) {
            $variations = $this->getVariationsForParentId($product->getId(), 1);
        }
        $variations->rewind();

        /** @var Product $variation */
        $variation = $variations->current();
        return $variation->getStock();
    }

    protected function getVariationsForParentId($parentId, $limit = 'all')
    {
        $filter = (new ProductFilter())
            ->setLimit($limit)
            ->setPage(1)
            ->setParentProductId([$parentId]);
        return $this->productService->fetchCollectionByFilter($filter);
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

    public function getIncPOStockInAvailableOptions(): array
    {
        if ($this->incPOStockInAvailableOptions) {
            return $this->incPOStockInAvailableOptions;
        }
        $productSettings = $this->getProductSettings();
        $defaultStockMode = $productSettings->isIncludePurchaseOrdersInAvailable();
        $defaultStockModeTitle = ($defaultStockMode ? 'On' : 'Off');
        $options = [
            // Can't use any value that equates to false (e.g. '') as then custom-select will replace it with the title
            ['value' => 'default', 'name' => 'Default (' . $defaultStockModeTitle . ')'],
            ['value' => 'on', 'name' => 'On'],
            ['value' => 'off', 'name' => 'Off'],
        ];
        $this->incPOStockInAvailableOptions = $options;
        return $this->incPOStockInAvailableOptions;
    }
    
    /**
     * @return self
     */
    protected function setUserOUService(UserOUService $userOUService)
    {
        $this->userOUService = $userOUService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setProductSettingsService(ProductSettingsService $productSettingsService)
    {
        $this->productSettingsService = $productSettingsService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setStockStorage(StockStorage $stockStorage)
    {
        $this->stockStorage = $stockStorage;
        return $this;
    }
}
