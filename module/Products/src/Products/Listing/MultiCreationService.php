<?php
namespace Products\Listing;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as Accounts;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Channel\Gearman\Generator\Listing\CreateListing as CreateListingJobGenerator;
use CG\Channel\Listing\Import\ProductDetail\Importer as ProductDetailImporter;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Product\AccountDetail\Entity as ProductAccountDetail;
use CG\Product\AccountDetail\Mapper as ProductAccountDetailMapper;
use CG\Product\AccountDetail\Service as ProductAccountDetailService;
use CG\Product\Category\Collection as Categories;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\Filter as CategoryFilter;
use CG\Product\Category\Service as CategoryService;
use CG\Product\Category\Template\Collection as CategoryTemplates;
use CG\Product\Category\Template\Entity as CategoryTemplate;
use CG\Product\Category\Template\Filter as CategoryTemplateFilter;
use CG\Product\Category\Template\Service as CategoryTemplateService;
use CG\Product\CategoryDetail\Entity as ProductCategoryDetail;
use CG\Product\CategoryDetail\Mapper as ProductCategoryDetailMapper;
use CG\Product\CategoryDetail\Service as ProductCategoryDetailService;
use CG\Product\ChannelDetail\Entity as ProductChannelDetail;
use CG\Product\ChannelDetail\Mapper as ProductChannelDetailMapper;
use CG\Product\ChannelDetail\Service as ProductChannelDetailService;
use CG\Product\Client\Service as ProductService;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Detail\Mapper as ProductDetailMapper;
use CG\Product\Entity as Product;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class MultiCreationService implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE_MISSING_ACCOUNT_IDS = 'No account ids specified - can\'t create listings';
    const LOG_MSG_MISSING_ACCOUNT_IDS = 'No account ids specified - can\'t create listings';
    const LOG_CODE_REQUESTED_ACCOUNTS_NOT_FOUND = 'Accounts not found - can\'t create listings';
    const LOG_MSG_REQUESTED_ACCOUNTS_NOT_FOUND = 'Accounts (%s) not found - can\'t create listings';
    const LOG_CODE_MISSING_CATEGORY_TEMPLATE_IDS = 'No category template ids specified - can\'t create listings';
    const LOG_MSG_MISSING_CATEGORY_TEMPLATE_IDS = 'No category template ids specified - can\'t create listings';
    const LOG_CODE_REQUESTED_CATEGORY_TEMPLATES_NOT_FOUND = 'Category templates not found - can\'t create listings';
    const LOG_MSG_REQUESTED_CATEGORY_TEMPLATES_NOT_FOUND = 'Category templates (%s) not found - can\'t create listings';
    const LOG_CODE_MISSING_CATEGORY_IDS = 'No category ids specified in category templates - can\'t create listings';
    const LOG_MSG_MISSING_CATEGORY_IDS = 'No category ids specified in category templates - can\'t create listings';
    const LOG_CODE_REQUESTED_CATEGORIES_NOT_FOUND = 'Categories not found - can\'t create listings';
    const LOG_MSG_REQUESTED_CATEGORIES_NOT_FOUND = 'Categories (%s) not found - can\'t create listings';
    const LOG_CODE_MISSING_PRODUCT_ID = 'No product id specified - can\'t create listings';
    const LOG_MSG_MISSING_PRODUCT_ID = 'No product id specified - can\'t create listings';
    const LOG_CODE_REQUESTED_PRODUCT_NOT_FOUND = 'Product not found - can\'t create listings';
    const LOG_MSG_REQUESTED_PRODUCT_NOT_FOUND = 'Product %d not found - can\'t create listings';
    const LOG_CODE_NO_VARIATIONS_SPECIFIED = 'No variaitions specified - can\'t create listings';
    const LOG_MSG_NO_VARIATIONS_SPECIFIED = 'No variaitions specified - can\'t create listings';
    const LOG_CODE_FAILED_TO_SAVE_PRODUCT_CHANNEL_DETAILS = 'Failed to save product channel details';
    const LOG_MSG_FAILED_TO_SAVE_PRODUCT_CHANNEL_DETAILS = 'Failed to save product channel (%s) details';
    const LOG_CODE_FAILED_TO_SAVE_PRODUCT_ACCOUNT_DETAILS = 'Failed to save product account details';
    const LOG_MSG_FAILED_TO_SAVE_PRODUCT_ACCOUNT_DETAILS = 'Failed to save product account (%d) details';
    const LOG_CODE_FAILED_TO_SAVE_PRODUCT_CATEGORY_DETAILS = 'Failed to save product category details';
    const LOG_MSG_FAILED_TO_SAVE_PRODUCT_CATEGORY_DETAILS = 'Failed to save product category (%d [%s]) details';
    const LOG_CODE_MULTIPLE_VARIATIONS = 'Multiple variations specified - assuming variation listing';
    const LOG_MSG_MULTIPLE_VARIATIONS = '%d variations specified - assuming variation listing';
    const LOG_CODE_VARIATION_SKU_MATCH = 'Single variation specified with same sku as product - assuming simple listing';
    const LOG_MSG_VARIATION_SKU_MATCH = 'Single variation specified with same sku (%s) as product - assuming simple listing';
    const LOG_CODE_VARIATION_SKU_DIFFERS = 'Single variation specified with different sku from product - assuming variation listing';
    const LOG_MSG_VARIATION_SKU_DIFFERS = 'Single variation specified with different sku (%s) from product - assuming variation listing';

    /** @var AccountService */
    protected $accountService;
    /** @var CategoryTemplateService */
    protected $categoryTemplateService;
    /** @var CategoryService */
    protected $categoryService;
    /** @var ProductService */
    protected $productService;
    /** @var ProductDetailMapper */
    protected $productDetailMapper;
    /** @var ProductDetailImporter */
    protected $productDetailImporter;
    /** @var ProductChannelDetailMapper */
    protected $productChannelDetailMapper;
    /** @var ProductChannelDetailService */
    protected $productChannelDetailService;
    /** @var ProductAccountDetailMapper */
    protected $productAccountDetailMapper;
    /** @var ProductAccountDetailService */
    protected $productAccountDetailService;
    /** @var ProductCategoryDetailMapper */
    protected $productCategoryDetailMapper;
    /** @var ProductCategoryDetailService */
    protected $productCategoryDetailService;
    /** @var CreateListingJobGenerator */
    protected $createListingJobGenerator;

    public function __construct(
        AccountService $accountService,
        CategoryTemplateService $categoryTemplateService,
        CategoryService $categoryService,
        ProductService $productService,
        ProductDetailMapper $productDetailMapper,
        ProductDetailImporter $productDetailImporter,
        ProductChannelDetailMapper $productChannelDetailMapper,
        ProductChannelDetailService $productChannelDetailService,
        ProductAccountDetailMapper $productAccountDetailMapper,
        ProductAccountDetailService $productAccountDetailService,
        ProductCategoryDetailMapper $productCategoryDetailMapper,
        ProductCategoryDetailService $productCategoryDetailService,
        CreateListingJobGenerator $createListingJobGenerator
    ) {
        $this->accountService = $accountService;
        $this->categoryTemplateService = $categoryTemplateService;
        $this->categoryService = $categoryService;
        $this->productService = $productService;
        $this->productDetailMapper = $productDetailMapper;
        $this->productDetailImporter = $productDetailImporter;
        $this->productChannelDetailMapper = $productChannelDetailMapper;
        $this->productChannelDetailService = $productChannelDetailService;
        $this->productAccountDetailMapper = $productAccountDetailMapper;
        $this->productAccountDetailService = $productAccountDetailService;
        $this->productCategoryDetailMapper = $productCategoryDetailMapper;
        $this->productCategoryDetailService = $productCategoryDetailService;
        $this->createListingJobGenerator = $createListingJobGenerator;
    }

    public function createListings(
        array $accountIds,
        array $categoryTemplateIds,
        string $siteId,
        array $productData,
        &$guid = null
    ): bool {
        $guid = uniqid('', true);

        $this->addGlobalLogEventParams(['account' => implode(',', $accountIds), 'categoryTemplate' => implode(', ', $categoryTemplateIds), 'site' => $siteId, 'guid' => $guid]);
        try {
            if (empty($accountIds)) {
                $this->logWarning(static::LOG_MSG_MISSING_ACCOUNT_IDS, [], static::LOG_CODE_MISSING_ACCOUNT_IDS);
                return false;
            }

            try {
                /** @var Accounts $accounts */
                $accounts = $this->accountService->fetchByFilter(
                    (new AccountFilter('all', 1))->setId($accountIds)
                );
            } catch (NotFound $exception) {
                $this->logWarningException($exception, static::LOG_MSG_REQUESTED_ACCOUNTS_NOT_FOUND, [implode(',', $accountIds)], static::LOG_CODE_REQUESTED_ACCOUNTS_NOT_FOUND);
                return false;
            }

            if (empty($categoryTemplateIds)) {
                $this->logWarning(static::LOG_MSG_MISSING_CATEGORY_TEMPLATE_IDS, [], static::LOG_CODE_MISSING_CATEGORY_TEMPLATE_IDS);
                return false;
            }

            try {
                /** @var CategoryTemplates $accounts */
                $categoryTemplates = $this->categoryTemplateService->fetchCollectionByFilter(
                    (new CategoryTemplateFilter('all', 1))->setId($categoryTemplateIds)
                );
            } catch (NotFound $exception) {
                $this->logWarningException($exception, static::LOG_MSG_REQUESTED_CATEGORY_TEMPLATES_NOT_FOUND, [implode(',', $categoryTemplateIds)], static::LOG_CODE_REQUESTED_CATEGORY_TEMPLATES_NOT_FOUND);
                return false;
            }

            $categoryIds = array_merge(...array_map(function(CategoryTemplate $categoryTemplate) {
                return $categoryTemplate->getCategoryIds();
            }, iterator_to_array($categoryTemplates)));

            if (empty($categoryIds)) {
                $this->logWarning(static::LOG_MSG_MISSING_CATEGORY_IDS, [], static::LOG_CODE_MISSING_CATEGORY_IDS);
                return false;
            }

            $this->addGlobalLogEventParam('category', implode(', ', $categoryIds));
            try {
                /** @var Categories $categories */
                $categories = $this->categoryService->fetchCollectionByFilter(
                    (new CategoryFilter('all', 1))->setId($categoryIds)
                );
            } catch (NotFound $exception) {
                $this->logWarningException($exception, static::LOG_MSG_REQUESTED_ACCOUNTS_NOT_FOUND, [implode(',', $categoryIds)], static::LOG_CODE_REQUESTED_ACCOUNTS_NOT_FOUND);
                return false;
            }

            $productId = $productData['id'] ?? null;
            if (!$productId) {
                $this->logWarning(static::LOG_MSG_MISSING_PRODUCT_ID, [], static::LOG_CODE_MISSING_PRODUCT_ID);
                return false;
            }

            $this->addGlobalLogEventParam('product', $productId);
            try {
                /** @var Product $product */
                $product = $this->productService->fetch($productId);
            } catch (NotFound $exception) {
                $this->logWarningException($exception, static::LOG_MSG_REQUESTED_PRODUCT_NOT_FOUND, [$productId], static::LOG_CODE_REQUESTED_PRODUCT_NOT_FOUND);
                return false;
            }

            $variationsData = $productData['variations'] ?? [];
            if (empty($variationsData)) {
                $this->logWarning(static::LOG_MSG_NO_VARIATIONS_SPECIFIED, [], static::LOG_CODE_NO_VARIATIONS_SPECIFIED);
                return false;
            }

            $skus = array_filter(array_map(function(array $variationData) {
                return $variationData['sku'] ?? null;
            }, $variationsData));
            $this->addGlobalLogEventParam('sku', implode(', ', $skus));

            $this->saveProductDetails($product, $productData, $variationsData);
            $this->saveProductChannelDetails($accounts->getArrayOf('channel'), $product, $productData);
            $this->saveProductAccountDetails($accounts, $product, $variationsData);
            $this->saveProductCategoryDetails($categories, $product, $productData);

            if ($this->isSimpleListing($product, $variationsData)) {
                $this->generateCreateSimpleListingJobs($accounts, $categories, $siteId, $product, $guid);
            } else {
                if (!$product->isParent()) {
                    $variations = [$product];
                } else {
                    $variations = array_filter(iterator_to_array($product->getVariations()), function(Product $product) use($skus) {
                        return in_array($product->getSku(), $skus);
                    });
                }
                $this->generateCreateVariationListingJobs($accounts, $categories, $siteId, $variations, $guid);
            }

            return true;
        } finally {
            $this->removeGlobalLogEventParams(['account', 'categoryTemplate', 'site', 'guid', 'category', 'product', 'sku']);
        }
    }

    protected function isSimpleListing(Product $product, array $variations): bool
    {
        if (($count = count($variations)) != 1) {
            $this->logDebug(static::LOG_MSG_MULTIPLE_VARIATIONS, [$count], static::LOG_CODE_MULTIPLE_VARIATIONS);
            return false;
        }

        $sku = reset($variations)['sku'] ?? '';
        if ($product->getSku() == $sku) {
            $this->logDebug(static::LOG_MSG_VARIATION_SKU_MATCH, ['sku' => $sku], static::LOG_CODE_VARIATION_SKU_MATCH);
            return true;
        } else {
            $this->logDebug(static::LOG_MSG_VARIATION_SKU_DIFFERS, ['sku' => $sku], static::LOG_CODE_VARIATION_SKU_DIFFERS);
            return false;
        }
    }

    protected function mapProductDetails(int $ou, string $sku, array $productData, array $variationData): ProductDetail
    {
        return $this->productDetailMapper->fromArray([
            'organisationUnitId' => $ou,
            'sku' => $sku,
            'weight' => $variationData['weight'] ?? $productData['weight'] ?? null,
            'width' => $variationData['width'] ?? $productData['width'] ?? null,
            'height' => $variationData['height'] ?? $productData['height'] ?? null,
            'length' => $variationData['length'] ?? $productData['length'] ?? null,
            'description' => $variationData['description'] ?? $productData['description'] ?? null,
            'ean' => $variationData['ean'] ?? $productData['ean'] ?? null,
            'brand' => $variationData['brand'] ?? $productData['brand'] ?? null,
            'mpn' => $variationData['mpn'] ?? $productData['mpn'] ?? null,
            'asin' => $variationData['asin'] ?? $productData['asin'] ?? null,
            'price' => $variationData['price'] ?? $productData['price'] ?? null,
            'cost' => $variationData['cost'] ?? $productData['cost'] ?? null,
            'condition' => $variationData['condition'] ?? $productData['condition'] ?? null,
            'categoryTemplateIds' => $variationData['categoryTemplateIds'] ?? $productData['categoryTemplateIds'] ?? [],
            'upc' => $variationData['upc'] ?? $productData['upc'] ?? null,
            'isbn' => $variationData['isbn'] ?? $productData['isbn'] ?? null,
        ]);
    }

    protected function saveProductDetails(Product $product, array $productData, array $variationsData)
    {
        $productDetails = [];
        foreach ($variationsData as $variationData) {
            $sku = $variationData['sku'] ?? null;
            if (!$sku) {
                continue;
            }
            $productDetails[$sku] = $this->mapProductDetails(
                $product->getOrganisationUnitId(),
                $sku,
                $productData,
                $variationData
            );
        }

        /** @var Product[] $products */
        $products = $product->isParent() ? $product->getVariations() : [$product];
        foreach ($products as $product) {
            if (isset($productDetails[$product->getSku()])) {
                $this->productDetailImporter->import($product, $productDetails);
            }
        }
    }

    protected function mapProductChannelDetails(
        int $productId,
        string $channel,
        int $ou,
        array $productChannelData
    ): ProductChannelDetail {
        return $this->productChannelDetailMapper->fromArray([
            'productId' => $productId,
            'channel' => $channel,
            'organisationUnitId' => $ou,
            'external' => $productChannelData,
        ]);
    }

    protected function saveProductChannelDetails(array $channels, Product $product, array $productData)
    {
        foreach (($productData['productChannelDetail'] ?? []) as $productChannelData) {
            $channel = $productChannelData['channel'] ?? null;
            if (!$channel || !in_array($channel, $channels)) {
                continue;
            }
            $this->saveProductChannelDetail(
                $this->mapProductChannelDetails(
                    $product->getId(),
                    $channel,
                    $product->getOrganisationUnitId(),
                    $productChannelData
                )
            );
        }
    }

    protected function saveProductChannelDetail(ProductChannelDetail $productChannelDetail)
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                // Copy current etag so we can safley overright current data
                $productChannelDetail->setStoredETag(
                    $this->productChannelDetailService->fetch($productChannelDetail->getId())->getStoredETag()
                );
            } catch (NotFound $exception) {
                // New entity - no etag to steal
            }

            try {
                $this->productChannelDetailService->save($productChannelDetail);
            } catch (NotModified $exception) {
                return;
            } catch (\Throwable $throwable) {
                $this->logCriticalException($throwable, static::LOG_MSG_FAILED_TO_SAVE_PRODUCT_CHANNEL_DETAILS, ['channel' => $productChannelDetail->getChannel()], static::LOG_CODE_FAILED_TO_SAVE_PRODUCT_CHANNEL_DETAILS);
            }
        }
    }

    protected function mapProductAccountDetails(
        int $productId,
        int $accountId,
        int $ou,
        array $variationAccountData
    ): ProductAccountDetail {
        return $this->productAccountDetailMapper->fromArray([
            'productId' => $productId,
            'accountId' => $accountId,
            'organisationUnitId' => $ou,
            'price' => $variationAccountData['price'] ?? null,
        ]);
    }

    protected function saveProductAccountDetails(Accounts $accounts, Product $product, array $variationsData)
    {
        $productAccountDetails = [];
        foreach ($variationsData as $variationData) {
            $sku = $variationData['sku'] ?? null;
            if (!$sku) {
                continue;
            }

            $productAccountDetails[$sku] = [];
            foreach (($variationData['productAccountDetail'] ?? []) as $productAccountDetail) {
                $accountId = $productAccountDetail['accountId'] ?? null;
                if (!$accountId) {
                    continue;
                }
                $account = $accounts->getById($accountId);
                if (!$account) {
                    continue;
                }
                $productAccountDetails[$sku][$accountId] = $productAccountDetail;
            }
        }


        /** @var Product[] $products */
        $products = $product->isParent() ? $product->getVariations() : [$product];
        foreach ($products as $product) {
            if (isset($productAccountDetails[$product->getSku()])) {
                foreach ($productAccountDetails[$product->getSku()] as $accountId => $variationAccountData) {
                    $this->saveProductAccountDetail(
                        $this->mapProductAccountDetails(
                            $product->getId(),
                            $accountId,
                            $product->getOrganisationUnitId(),
                            $variationAccountData
                        )
                    );
                }
            }
        }

        foreach (($productData['productChannelDetail'] ?? []) as $productChannelData) {
            $channel = $productChannelData['channel'] ?? null;
            if (!$channel) {
                continue;
            }
            $this->saveProductChannelDetail(
                $this->mapProductChannelDetails(
                    $product->getId(),
                    $channel,
                    $product->getOrganisationUnitId(),
                    $productChannelData
                )
            );
        }
    }

    protected function saveProductAccountDetail(ProductAccountDetail $productAccountDetail)
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                // Copy current etag so we can safley overright current data
                $productAccountDetail->setStoredETag(
                    $this->productAccountDetailService->fetch($productAccountDetail->getId())->getStoredETag()
                );
            } catch (NotFound $exception) {
                // New entity - no etag to steal
            }

            try {
                $this->productAccountDetailService->save($productAccountDetail);
            } catch (NotModified $exception) {
                return;
            } catch (\Throwable $throwable) {
                $this->logCriticalException($throwable, static::LOG_MSG_FAILED_TO_SAVE_PRODUCT_ACCOUNT_DETAILS, ['account' => $productAccountDetail->getAccountId()], static::LOG_CODE_FAILED_TO_SAVE_PRODUCT_ACCOUNT_DETAILS);
            }
        }
    }

    protected function mapProductCategoryDetails(
        int $productId,
        int $categoryId,
        string $channel,
        int $ou,
        array $productCategoryData
    ): ProductCategoryDetail {
        return $this->productCategoryDetailMapper->fromArray([
            'productId' => $productId,
            'categoryId' => $categoryId,
            'channel' => $channel,
            'organisationUnitId' => $ou,
            'external' => $productCategoryData,
        ]);
    }

    protected function saveProductCategoryDetails(Categories $categories, Product $product, array $productData)
    {
        foreach (($productData['productCategoryDetail'] ?? []) as $productCategoryData) {
            $categoryId = $productCategoryData['categoryId'] ?? null;
            if (!$categoryId) {
                continue;
            }
            /** @var Category $category */
            $category = $categories->getById($categoryId);
            if (!$category) {
                continue;
            }
            $this->saveCategoryChannelDetail(
                $this->mapProductCategoryDetails(
                    $product->getId(),
                    $categoryId,
                    $category->getChannel(),
                    $product->getOrganisationUnitId(),
                    $productCategoryData
                )
            );
        }
    }

    protected function saveCategoryChannelDetail(ProductCategoryDetail $productCategoryDetail)
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                // Copy current etag so we can safley overright current data
                $productCategoryDetail->setStoredETag(
                    $this->productCategoryDetailService->fetch($productCategoryDetail->getId())->getStoredETag()
                );
            } catch (NotFound $exception) {
                // New entity - no etag to steal
            }

            try {
                $this->productCategoryDetailService->save($productCategoryDetail);
            } catch (NotModified $exception) {
                return;
            } catch (\Throwable $throwable) {
                $this->logCriticalException($throwable, static::LOG_MSG_FAILED_TO_SAVE_PRODUCT_CATEGORY_DETAILS, ['category' => $productCategoryDetail->getCategoryId(), 'channel' => $productCategoryDetail->getChannel()], static::LOG_CODE_FAILED_TO_SAVE_PRODUCT_CATEGORY_DETAILS);
            }
        }
    }

    /**
     * @param Account[] $accounts
     * @param Category[] $categories
     */
    protected function getAccountCategoryIterator(Accounts $accounts, Categories $categories): \Generator
    {
        foreach ($categories as $category) {
            $accountId = $category->getAccountId();
            if ($accountId) {
                $account = $accounts->getById($accountId);
                if ($account) {
                    yield [$account, $category];
                }
            } else {
                foreach ($accounts->getBy('channel', $category->getChannel()) as $account) {
                    yield [$account, $category];
                }
            }
        }
    }

    protected function generateCreateSimpleListingJobs(
        Accounts $accounts,
        Categories $categories,
        string $siteId,
        Product $product,
        string $guid
    ) {
        /**
         * @var Account $account
         * @var Category $category
         */
        foreach ($this->getAccountCategoryIterator($accounts, $categories) as [$account, $category]) {
            $this->createListingJobGenerator->generateJob(
                $account,
                $category,
                $product,
                $siteId,
                $guid
            );
        }
    }

    /**
     * @param Product[] $variations
     */
    protected function generateCreateVariationListingJobs(
        Accounts $accounts,
        Categories $categories,
        Product $product,
        string $siteId,
        array $variations,
        string $guid
    ) {
        /**
         * @var Account $account
         * @var Category $category
         */
        foreach ($this->getAccountCategoryIterator($accounts, $categories) as [$account, $category]) {
            $this->createListingJobGenerator->generateJob(
                $account,
                $category,
                $product,
                $siteId,
                $guid,
                $this->extractVariationProductIds($variations)
            );
        }
    }

    /**
     * @param Product[] $variations
     * @return array
     */
    protected function extractVariationProductIds(array $variations)
    {
        $ids = [];
        foreach ($variations as $variation) {
            $ids[$variation->getId()] = $variation->getId();
        }
        return $ids;
    }
}