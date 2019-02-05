<?php

namespace Products\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Http\StatusCode;
use CG\Image\Uploader as ImageUploader;
use CG\Listing\Entity as ListingEntity;
use CG\Listing\StatusHistory\Entity as ListingStatusHistory;
use CG\Location\Service as LocationService;
use CG\Location\Type as LocationType;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\Entity as ProductEntity;
use CG\Product\Exception\ProductLinkBlockingProductDeletionException;
use CG\Product\Filter\Mapper as FilterMapper;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\ValidationException;
use CG\Stock\Import\UpdateOptions as StockImportUpdateOptions;
use CG\Stock\Location\Service as StockLocationService;
use CG\User\ActiveUserInterface;
use CG\Zend\Stdlib\Http\FileResponse;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use CG_Usage\Service as UsageService;
use CG\Product\Csv\Stock\Service as StockCsvService;
use CG\Product\Csv\Link\Service as ProductLinkCsvService;
use Products\Listing\Channel\Service as ListingChannelService;
use Products\Product\Creator as ProductCreator;
use Products\Product\Link\Service as ProductLinkService;
use Products\Product\Service as ProductService;
use Products\Product\TaxRate\Service as TaxRateService;
use Products\Stock\Settings\Service as StockSettingsService;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Product\Link\Gearman\Workload\ExportProductLinks as ExportProductLinksWorkload;
use GearmanClient;

class ProductsJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';
    const ROUTE_AJAX_TAX_RATE = 'tax_rate';
    const ROUTE_STOCK_MODE = 'Stock Mode';
    const ROUTE_STOCK_LEVEL = 'Stock Level';
    const ROUTE_STOCK_UPDATE = 'stockupdate';
    const ROUTE_STOCK_CSV_EXPORT = 'stockCsvExport';
    const ROUTE_STOCK_CSV_EXPORT_CHECK = 'stockCsvExportCheck';
    const ROUTE_STOCK_CSV_EXPORT_PROGRESS = 'stockCsvExportProgress';
    const ROUTE_STOCK_CSV_IMPORT = 'stockCsvImport';
    const ROUTE_PRODUCT_LINK_CSV_EXPORT = 'productLinkCsvExport';
    const ROUTE_PRODUCT_LINK_CSV_IMPORT = 'productLinkCsvImport';
    const ROUTE_DELETE = 'Delete';
    const ROUTE_DELETE_CHECK = 'Delete Check';
    const ROUTE_DELETE_PROGRESS = 'Delete Progress';
    const ROUTE_DETAILS_UPDATE = 'detailsUpdate';
    const ROUTE_NEW_NAME = 'newName';
    const ROUTE_STOCK_FETCH = 'StockFetch';
    const ROUTE_IMAGE_UPLOAD = 'Image Upload';
    const ROUTE_CREATE = 'Create';

    const PROGRESS_KEY_NAME_STOCK_EXPORT = 'stockExportProgressKey';

    /** @var ProductService $productService */
    protected $productService;
    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;
    /** @var FilterMapper $filterMapper */
    protected $filterMapper;
    /** @var Translator $translator */
    protected $translator;
    /** @var AccountService $accountService */
    protected $accountService;
    /** @var TaxRateService $taxRateService */
    protected $taxRateService;
    /** @var OrganisationUnitService $organisationUnitService */
    protected $organisationUnitService;
    /** @var StockCsvService $stockCsvService */
    protected $stockCsvService;
    /** @var ProductLinkCsvService */
    protected $productLinkCsvService;
    /** @var StockSettingsService */
    protected $stockSettingsService;
    /** @var UsageService */
    protected $usageService;
    /** @var LocationService */
    protected $locationService;
    /** @var StockLocationService */
    protected $stockLocationService;
    /** @var ActiveUserInterface */
    protected $activeUser;
    /** @var ProductLinkService */
    protected $productLinkService;
    /** @var ListingChannelService */
    protected $listingChannelService;
    /** @var ImageUploader */
    protected $imageUploader;
    /** @var ProductCreator */
    protected $productCreator;
    /** @var GearmanClient */
    protected $productsGearmanClient;

    public function __construct(
        ProductService $productService,
        JsonModelFactory $jsonModelFactory,
        FilterMapper $filterMapper,
        Translator $translator,
        AccountService $accountService,
        TaxRateService $taxRateService,
        OrganisationUnitService $organisationUnitService,
        StockCsvService $stockCsvService,
        ProductLinkCsvService $productLinkCsvService,
        StockSettingsService $stockSettingsService,
        UsageService $usageService,
        LocationService $locationService,
        StockLocationService $stockLocationService,
        ActiveUserInterface $activeUser,
        ProductLinkService $productLinkService,
        ListingChannelService $listingChannelService,
        ImageUploader $imageUploader,
        ProductCreator $productCreator,
        GearmanClient $productsGearmanClient
    ) {
        $this->productService = $productService;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->filterMapper = $filterMapper;
        $this->translator = $translator;
        $this->accountService = $accountService;
        $this->taxRateService = $taxRateService;
        $this->organisationUnitService = $organisationUnitService;
        $this->stockCsvService = $stockCsvService;
        $this->productLinkCsvService = $productLinkCsvService;
        $this->stockSettingsService = $stockSettingsService;
        $this->usageService = $usageService;
        $this->locationService = $locationService;
        $this->stockLocationService = $stockLocationService;
        $this->activeUser = $activeUser;
        $this->productLinkService = $productLinkService;
        $this->listingChannelService = $listingChannelService;
        $this->imageUploader = $imageUploader;
        $this->productCreator = $productCreator;
        $this->productsGearmanClient = $productsGearmanClient;
    }

    public function ajaxAction()
    {
        $view = $this->jsonModelFactory->newInstance();
        $filterParams = $this->params()->fromPost('filter', []);
        $page = (isset($filterParams['page']) ? $filterParams['page'] : ProductService::PAGE);
        $limit = 'all';
        if (
            !array_key_exists('parentProductId', $filterParams)
            && !array_key_exists('id', $filterParams)
            && !array_key_exists('replaceVariationWithParent', $filterParams)
        ) {
            $limit = (isset($filterParams['limit']) ? $filterParams['limit'] : ProductService::LIMIT);
            $filterParams['replaceVariationWithParent'] = true;
        }
        if (!array_key_exists('deleted', $filterParams)) {
            $filterParams['deleted'] = false;
        }
        $requestFilter = $this->filterMapper->fromArray($filterParams);
        $requestFilter->setEmbedVariationsAsLinks(true);
        $total = 0;
        $productsArray = [];
        try {
            $products = $this->productService->fetchProducts($requestFilter, $limit, $page);
            $organisationUnitIds = $requestFilter->getOrganisationUnitId();
            $accounts = $this->fetchAccounts($organisationUnitIds);
            $accountsArray = $this->getAccountsIndexedById($accounts);
            $rootOrganisationUnit = $this->organisationUnitService->getRootOuFromOuId(reset($organisationUnitIds));
            $merchantLocationIds = $this->locationService->fetchIdsByType(
                [LocationType::MERCHANT],
                $rootOrganisationUnit->getId()
            );

            $allowedCreateListingChannels = $this->listingChannelService->getAllowedCreateListingsChannels($rootOrganisationUnit);
            $allowedCreateListingVariationsChannels = $this->listingChannelService->getAllowedCreateListingsVariationsChannels($rootOrganisationUnit);
            foreach ($products as $product) {
                $productsArray[] = $this->toArrayProductEntityWithEmbeddedData(
                    $product,
                    $accountsArray,
                    $rootOrganisationUnit,
                    $merchantLocationIds
                );
            }
            $total = $products->getTotal();

            $productSearchActive = $this->listingChannelService->isProductSearchActive($rootOrganisationUnit);
            $productSearchActiveForVariations = $this->listingChannelService->isProductSearchActiveForVariations($rootOrganisationUnit);

            $skuThatProductsCantLinkFrom = $filterParams['skuThatProductsCantLinkFrom'] ?? null;
            if ($skuThatProductsCantLinkFrom) {
                $view->setVariable(
                    'nonLinkableSkus',
                    $this->productLinkService->getSkusProductCantLinkTo(
                        $products->getFirst()->getOrganisationUnitId(),
                        $skuThatProductsCantLinkFrom
                    )
                );
            }
        } catch(NotFound $e) {
            $allowedCreateListingChannels = [];
            $allowedCreateListingVariationsChannels = [];
            $accountsArray = [];
            $productSearchActive = false;
            $productSearchActiveForVariations = false;
        }

        $view
            ->setVariable('products', $productsArray)
            ->setVariable('accounts', $accountsArray)
            ->setVariable('createListingsAllowedChannels', $allowedCreateListingChannels)
            ->setVariable('createListingsAllowedVariationChannels', $allowedCreateListingVariationsChannels)
            ->setVariable('productSearchActive', $productSearchActive)
            ->setVariable('productSearchActiveForVariations', $productSearchActiveForVariations)
            ->setVariable('pagination', ['page' => (int)$page, 'limit' => (int)$limit, 'total' => (int)$total]);
        return $view;
    }

    protected function fetchAccounts($organisationUnitIds): AccountCollection
    {
        return $accounts = $this->accountService->fetchByOU($organisationUnitIds, 'all');
    }

    protected function getAccountsIndexedById(AccountCollection $accounts): array
    {
        $indexedAccounts = [];
        /** @var Account $account */
        foreach($accounts as $account) {
            $indexedAccounts[$account->getId()] = $this->listingChannelService->getAccountData($account);
        }
        return $indexedAccounts;
    }

    protected function toArrayProductEntityWithEmbeddedData(
        ProductEntity $productEntity,
        array $accounts,
        OrganisationUnit $rootOrganisationUnit,
        array $merchantLocationIds
    ) {
        $product = $productEntity->toArray();

        $activeSalesAccounts = $this->getActiveSalesAccounts($accounts);

        $product = array_merge($product, [
            'eTag' => $productEntity->getStoredETag(),
            'images' => [],
            'listings' => $this->getProductListingsArray($productEntity),
            'listingsPerAccount' => $this->getProductListingsPerAccountArray($productEntity, $activeSalesAccounts),
            'activeSalesAccounts' => $activeSalesAccounts,
            'accounts' => $accounts,
            'stockModeDefault' => $this->stockSettingsService->getStockModeDefault(),
            'stockLevelDefault' => $this->stockSettingsService->getStockLevelDefault(),
        ]);

        $images = array_column($productEntity->getImageIds(), 'id', 'order');
        ksort($images, SORT_NUMERIC);
        foreach ($images as $imageId) {
            $image = $productEntity->getImages()->getById($imageId);
            if ($image) {
                $product['images'][] = $image->toArray();
            }
        }

        if (!$productEntity->isParent()) {
            $product = array_merge(
                $product,
                [
                    'stockModeDesc' => $this->stockSettingsService->getStockModeDecriptionForProduct($productEntity),
                    'stockModeOptions' => $this->stockSettingsService->getStockModeOptionsForProduct($productEntity),
                ]
            );
        }

        if($rootOrganisationUnit->isVatRegistered()) {
            $product['taxRates'] = $this->taxRateService->getTaxRatesOptionsForProduct($productEntity, $rootOrganisationUnit);
        }

        $product['variationCount'] = count($productEntity->getVariationIds());
        $product['variationIds'] = $productEntity->getVariationIds();

        if (!$productEntity->getStock() || count($productEntity->getVariations())) {
            return $product;
        }

        $stockEntity = $productEntity->getStock();
        $product['stock'] = array_merge($stockEntity->toArray(), [
            'locations' => $this->stockLocationService
                ->getFromCollectionByLocationIds($stockEntity->getLocations(), $merchantLocationIds)
                ->toArray()
        ]);

        $detailsEntity = $productEntity->getDetails();
        if ($detailsEntity) {
            $locale = $rootOrganisationUnit->getLocale();
            $product['details'] = [
                'id' => $detailsEntity->getId(),
                'sku' => $detailsEntity->getSku(),
                'weight' => $detailsEntity->getDisplayWeight($locale),
                'width' => $detailsEntity->getDisplayWidth($locale),
                'height' => $detailsEntity->getDisplayHeight($locale),
                'length' => $detailsEntity->getDisplayLength($locale),
                'price' => $detailsEntity->getPrice(),
                'description' => $detailsEntity->getDescription(),
                'condition' => $detailsEntity->getCondition(),
                'brand' => $detailsEntity->getBrand(),
                'mpn' => $detailsEntity->getMpn(),
                'ean' => $detailsEntity->getEan(),
                'upc' => $detailsEntity->getUpc(),
                'isbn' => $detailsEntity->getIsbn(),
                'barcodeNotApplicable' => $detailsEntity->isBarcodeNotApplicable(),
            ];
        } else {
            $product['details'] = ['sku' => $productEntity->getSku()];
        }

        foreach ($product['stock']['locations'] as $stockLocationIndex => $stockLocation) {
            $stockLocationId = $product['stock']['locations'][$stockLocationIndex]['id'];
            $product['stock']['locations'][$stockLocationIndex]['eTag'] = $stockEntity->getLocations()->getById($stockLocationId)->getStoredETag();
        }
        return $product;
    }

    protected function getActiveSalesAccounts($accounts)
    {
        $activeSalesAccounts = [];
        foreach ($accounts as $account) {
            if ($account['deleted'] || (! $account['active']) || (! in_array('sales', $account['type']))) {
                continue;
            }
            $activeSalesAccounts[$account['id']] = $account;
        }
        return $activeSalesAccounts;
    }

    protected function getProductListingsPerAccountArray(ProductEntity $productEntity, $accounts)
    {
        $listingIdsByAccountId = [];
        /** @var ListingEntity $listing */
        foreach ($productEntity->getListings() as $listing) {
            $accountId = $listing->getAccountId();
            if (!isset($listingIdsByAccountId[$accountId])) {
                $listingIdsByAccountId[$accountId] = [];
            }
            $listingIdsByAccountId[$accountId][] = $listing->getId();
        }
        return $listingIdsByAccountId;
    }

    protected function getProductListingsArray(ProductEntity $productEntity)
    {
        $listings = [];
        /** @var ListingEntity $listing */
        foreach ($productEntity->getListings() as $listing) {
            $id = $listing->getId();
            $listingData = $listing->toArray();
            $listingData['message'] = '';

            $statusHistory = $listing->getStatusHistory();
            $statusHistory->rewind();

            if ($statusHistory->count() == 0) {
                $listings[$id] = $listingData;
                continue;
            }

            /** @var ListingStatusHistory $currentStatus */
            $currentStatus = $statusHistory->current();
            if ($currentStatus->getStatus() != $listing->getStatus()) {
                $listings[$id] = $listingData;
                continue;
            }

            $listingData['message'] = $currentStatus->getMessage();
            $listings[$id] = $listingData;
        }
        return $listings;
    }

    public function stockFetchAction()
    {
        $view = $this->jsonModelFactory->newInstance();
        $productSku = $this->params()->fromRoute('productSku');

        try {
            $stock = $this->productService->fetchStockForSku($productSku, $this->activeUser->getActiveUserRootOrganisationUnitId());
        } catch(ValidationException $e) {
            $this->getResponse()->setStatusCode(StatusCode::UNPROCESSABLE_ENTITY);
            return $view;
        }

        $view->setVariables(['stock' => $stock]);
        return $view;
    }

    public function stockUpdateAction()
    {
        $this->checkUsage();

        $view = $this->jsonModelFactory->newInstance();
        try {
            $stockLocation = $this->productService->updateStock(
                $this->params()->fromPost('stockLocationId'),
                $this->params()->fromPost('eTag'),
                $this->params()->fromPost('totalQuantity')
            );
            $view->setVariable('eTag', $stockLocation->getStoredETag());
        } catch (NotModified $e) {
            $view->setVariable('code', StatusCode::NOT_MODIFIED);
            $view->setVariable('message', $this->translator->translate('There were no changes to be saved'));
        }

        return $view;
    }

    public function deleteCheckAction()
    {
        $this->checkUsage();
        return $this->jsonModelFactory->newInstance(
            ["allowed" => true, "guid" => uniqid('', true), "total" => count($this->params()->fromPost('productIds'))]
        );
    }

    public function deleteAction()
    {
        $view = $this->jsonModelFactory->newInstance();

        $productIds = $this->params()->fromPost('productIds');
        if (empty($productIds)){
            return $view;
        }

        try {
            $this->productService->checkForSafeDeletionWithProductLinks($productIds);
        } catch (ProductLinkBlockingProductDeletionException $exception) {
            $this->getResponse()->setStatusCode(StatusCode::UNPROCESSABLE_ENTITY);
            return $view->setVariables([
                'nonDeletableSkuList' => $exception->getNonDeletableSkuList(),
                'listOfAncestorSkusWithDeletionPreventingLinks' => $exception->getAncestorSkusWithDeletionPreventingLinks()
            ]);
        }

        $progressKey = $this->params()->fromPost('progressKey');
        $this->productService->deleteProductsById($productIds, $progressKey);
        return $view;
    }

    public function deleteProgressAction()
    {
        $progressKey = $this->params()->fromPost('progressKey');
        $progressCount = $this->productService->checkProgressOfDeleteProducts($progressKey);
        return $this->jsonModelFactory->newInstance([
            'progressCount' => $progressCount
        ]);
    }

    public function saveProductTaxRateAction()
    {
        $this->checkUsage();

        $productId = (int) $this->params()->fromPost('productId');
        $taxRateId = (string) $this->params()->fromPost('taxRateId');
        $memberState = (string) $this->params()->fromPost('memberState');
        $view = $this->jsonModelFactory->newInstance();
        $this->productService->saveProductTaxRateId($productId, $taxRateId, $memberState);
        $view->setVariable('saved', true);
        return $view;
    }

    public function saveProductStockModeAction()
    {
        $this->checkUsage();

        $productId = $this->params()->fromPost('id');
        $stockMode = $this->params()->fromPost('stockMode');
        if ($stockMode === 'null') {
            $stockMode = null;
        }

        return $this->jsonModelFactory->newInstance(
            $this->stockSettingsService->saveProductStockMode($productId, $stockMode)
        );
    }

    public function saveProductStockLevelAction()
    {
        $this->checkUsage();

        $productId = $this->params()->fromPost('id');
        $stockLevel = $this->params()->fromPost('stockLevel');

        return $this->jsonModelFactory->newInstance(
            $this->stockSettingsService->saveProductStockLevel($productId, $stockLevel)
        );
    }

    public function saveProductNameAction()
    {
        $this->checkUsage();

        $productId = $this->params()->fromPost('id');
        $name = $this->params()->fromPost('name');

        return $this->jsonModelFactory->newInstance(
            $this->productService->saveProductName($productId, $name)
        );
    }

    public function stockCsvExportAction()
    {
        try {
            $guid = $this->params()->fromPost(static::PROGRESS_KEY_NAME_STOCK_EXPORT);
            $csv = $this->stockCsvService->generateCsvForActiveUser($guid);
            return new FileResponse(StockCsvService::MIME_TYPE, StockCsvService::FILENAME, (string) $csv);
        } catch (NotFound $exception) {
            return $this->redirect()->toRoute('Products');
        }
    }

    public function linkCsvExportAction()
    {

        $rootOuId = $this->activeUser->getActiveUserRootOrganisationUnitId();
        $userName = $this->activeUser->getActiveUser()->getUsername();

        $workload = new ExportProductLinksWorkload($rootOuId, $userName);
        $this->productsGearmanClient->doBackground(ExportProductLinksWorkload::FUNCTION_NAME, serialize($workload), ExportProductLinksWorkload::FUNCTION_NAME . $rootOuId);

        return $this->jsonModelFactory->newInstance(['email' => $userName]);
    }

    public function stockCsvExportCheckAction()
    {
        $this->checkUsage();

        $guid = uniqid('', true);
        $this->stockCsvService->startProgress($guid);
        return $this->jsonModelFactory->newInstance(
            ["allowed" => true, "guid" => $guid]
        );
    }

    public function stockCsvExportProgressAction()
    {
        $guid = $this->params()->fromPost(static::PROGRESS_KEY_NAME_STOCK_EXPORT);
        $count = $this->stockCsvService->checkProgress($guid);
        $total = $this->stockCsvService->getTotalForProgress($guid);
        return $this->jsonModelFactory->newInstance(
            ["progressCount" => $count, 'total' => $total]
        );
    }

    public function stockCsvImportAction()
    {
        $this->checkUsage();

        $request = $this->getRequest();
        $post = $request->getPost()->toArray();

        if (!(isset($post["updateOption"]) && StockImportUpdateOptions::isValid($post["updateOption"]))) {
            throw new \RuntimeException("Missing/Invalid update option provided");
        }

        if (!isset($post['stockUploadFile'])) {
            throw new \RuntimeException("No File uploaded");
        }

        $this->stockCsvService->uploadCsvForActiveUser($post["updateOption"], $post['stockUploadFile']);

        $view = $this->jsonModelFactory->newInstance();
        $view->setVariable("success", true);
        return $view;
    }

    public function linkCsvImportAction()
    {
        $this->checkUsage();
        $request = $this->getRequest();
        $post = $request->getPost()->toArray();

        if (!isset($post['productLinkUploadFile'])) {
            throw new \RuntimeException('No file uploaded');
        }

        $this->productLinkCsvService->uploadCsvForActiveUser($post['productLinkUploadFile']);

        $view = $this->jsonModelFactory->newInstance();
        $view->setVariable('success', true);
        return $view;
    }

    public function detailsUpdateAction()
    {
        $this->checkUsage();

        $view = $this->jsonModelFactory->newInstance();
        $view->setVariable(
            'id',
            $this->productService->saveProductDetail(
                $this->params()->fromPost('sku'),
                $this->params()->fromPost('detail'),
                $this->params()->fromPost('value'),
                $this->params()->fromPost('id')
            )
        );

        return $view;
    }

    public function imageUploadAction()
    {
        $this->checkUsage();

        $imageData = $this->params()->fromPost('image');
        $filename = $this->params()->fromPost('filename');
        if (!$imageData || !$filename) {
            return $this->jsonModelFactory->newInstance([
                'success' => false,
                'error' => 'No image data or filename was supplied',
            ]);
        }

        $rootOuId = $this->activeUser->getActiveUserRootOrganisationUnitId();
        $rootOu = $this->organisationUnitService->fetch($rootOuId);
        $filenameParts = explode('.', $filename);
        $extension = array_pop($filenameParts);
        $image = ($this->imageUploader)($rootOu, base64_decode($imageData), $extension);

        return $this->jsonModelFactory->newInstance([
            'success' => true,
            'id' => $image->getId(),
            'url' => $image->getUrl()
        ]);
    }

    public function createAction()
    {
        $this->checkUsage();

        $productData = $this->params()->fromPost('product');
        if (!$productData) {
            return $this->jsonModelFactory->newInstance([
                'success' => false,
                'error' => 'No product data was supplied'
            ]);
        }

        try {
            $product = $this->productCreator->createFromUserInput($productData);
            return $this->jsonModelFactory->newInstance([
                'success' => true,
                'id' => $product->getId(),
                'etag' => $product->getStoredETag(),
                'error' => ''
            ]);
        } catch (ValidationException $e) {
            return $this->jsonModelFactory->newInstance([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function checkUsage()
    {
        if ($this->usageService->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }
    }
}
