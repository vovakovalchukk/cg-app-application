<?php

namespace Products\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Http\StatusCode;
use CG\Listing\Entity as ListingEntity;
use CG\Listing\StatusHistory\Entity as ListingStatusHistory;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\Entity as ProductEntity;
use CG\Product\Filter\Mapper as FilterMapper;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Import\UpdateOptions as StockImportUpdateOptions;
use CG\Zend\Stdlib\Http\FileResponse;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use CG_Usage\Service as UsageService;
use Products\Product\Service as ProductService;
use Products\Product\TaxRate\Service as TaxRateService;
use Products\Stock\Csv\Service as StockCsvService;
use Products\Stock\Settings\Service as StockSettingsService;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\Controller\AbstractActionController;

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
    const ROUTE_DELETE = 'Delete';
    const ROUTE_DELETE_CHECK = 'Delete Check';
    const ROUTE_DELETE_PROGRESS = 'Delete Progress';
    const ROUTE_DETAILS_UPDATE = 'detailsUpdate';
    const ROUTE_NEW_NAME = 'newName';

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
    /** @var StockSettingsService */
    protected $stockSettingsService;
    /** @var UsageService */
    protected $usageService;

    public function __construct(
        ProductService $productService,
        JsonModelFactory $jsonModelFactory,
        FilterMapper $filterMapper,
        Translator $translator,
        AccountService $accountService,
        TaxRateService $taxRateService,
        OrganisationUnitService $organisationUnitService,
        StockCsvService $stockCsvService,
        StockSettingsService $stockSettingsService,
        UsageService $usageService
    ) {
        $this->setProductService($productService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setFilterMapper($filterMapper)
            ->setTranslator($translator)
            ->setAccountService($accountService)
            ->setTaxRateService($taxRateService)
            ->setOrganisationUnitService($organisationUnitService)
            ->setStockCsvService($stockCsvService)
            ->setStockSettingsService($stockSettingsService)
            ->setUsageService($usageService);
    }

    public function ajaxAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        $filterParams = $this->params()->fromPost('filter', []);
        $page = (isset($filterParams['page']) ? $filterParams['page'] : ProductService::PAGE);
        $limit = 'all';
        if (!array_key_exists('parentProductId', $filterParams) && !array_key_exists('id', $filterParams)) {
            $limit = (isset($filterParams['limit']) ? $filterParams['limit'] : ProductService::LIMIT);
            $filterParams['replaceVariationWithParent'] = true;
        }
        if (!array_key_exists('deleted', $filterParams)) {
            $filterParams['deleted'] = false;
        }
        $requestFilter = $this->getFilterMapper()->fromArray($filterParams);
        $requestFilter->setEmbedVariationsAsLinks(true);
        $total = 0;
        $productsArray = [];
        $accounts = [];
        try {
            $products = $this->getProductService()->fetchProducts($requestFilter, $limit, $page);
            $organisationUnitIds = $requestFilter->getOrganisationUnitId();
            $accounts = $this->getAccountsIndexedById($organisationUnitIds);
            $rootOrganisationUnit = $this->organisationUnitService->getRootOuFromOuId(reset($organisationUnitIds));

            foreach ($products as $product) {
                $productsArray[] = $this->toArrayProductEntityWithEmbeddedData($product, $accounts, $rootOrganisationUnit);
            }
            $total = $products->getTotal();
        } catch(NotFound $e) {
            //noop
        }
        $view
            ->setVariable('products', $productsArray)
            ->setVariable('pagination', ['page' => (int)$page, 'limit' => (int)$limit, 'total' => (int)$total]);
        return $view;
    }

    protected function getAccountsIndexedById($organisationUnitIds)
    {
        $accounts = $this->getAccountService()->fetchByOU($organisationUnitIds, 'all');
        $indexedAccounts = [];
        foreach($accounts as $account) {
            $indexedAccounts[$account->getId()] = $account->toArray();
        }
        return $indexedAccounts;
    }

    protected function toArrayProductEntityWithEmbeddedData(ProductEntity $productEntity, $accounts, $rootOrganisationUnit)
    {
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
            $product['taxRates'] = $this->taxRateService->getTaxRatesOptionsForProduct($productEntity, $rootOrganisationUnit->getMemberState());
        }

        $product['variationCount'] = count($productEntity->getVariationIds());
        $product['variationIds'] = $productEntity->getVariationIds();

        if (!$productEntity->getStock() || count($productEntity->getVariations())) {
            return $product;
        }

        $stockEntity = $productEntity->getStock();
        $product['stock'] = array_merge($productEntity->getStock()->toArray(), [
            'locations' => $stockEntity->getLocations()->toArray()
        ]);

        $detailsEntity = $productEntity->getDetails();
        if ($detailsEntity) {
            $product['details'] = [
                'id' => $detailsEntity->getId(),
                'sku' => $detailsEntity->getSku(),
                'weight' => $detailsEntity->getDisplayWeight(),
                'width' => $detailsEntity->getDisplayWidth(),
                'height' => $detailsEntity->getDisplayHeight(),
                'length' => $detailsEntity->getDisplayLength(),
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

    public function stockUpdateAction()
    {
        $this->checkUsage();

        $view = $this->getJsonModelFactory()->newInstance();
        try {
            $stockLocation = $this->getProductService()->updateStock(
                $this->params()->fromPost('stockLocationId'),
                $this->params()->fromPost('eTag'),
                $this->params()->fromPost('totalQuantity')
            );
            $view->setVariable('eTag', $stockLocation->getStoredETag());
        } catch (NotModified $e) {
            $view->setVariable('code', StatusCode::NOT_MODIFIED);
            $view->setVariable('message', $this->getTranslator()->translate('There were no changes to be saved'));
        }

        return $view;
    }

    public function deleteCheckAction()
    {
        $this->checkUsage();
        return $this->getJsonModelFactory()->newInstance(
            ["allowed" => true, "guid" => uniqid('', true), "total" => count($this->params()->fromPost('productIds'))]
        );
    }

    public function deleteAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();

        $productIds = $this->params()->fromPost('productIds');
        if (empty($productIds)){
            return $view;
        }

        $progressKey = $this->params()->fromPost('progressKey');
        $this->getProductService()->deleteProductsById($productIds, $progressKey);
        return $view;
    }

    public function deleteProgressAction()
    {
        $progressKey = $this->params()->fromPost('progressKey');
        $progressCount = $this->getProductService()->checkProgressOfDeleteProducts($progressKey);
        return $this->getJsonModelFactory()->newInstance([
            'progressCount' => $progressCount
        ]);
    }

    public function saveProductTaxRateAction()
    {
        $this->checkUsage();

        $productId = (int) $this->params()->fromPost('productId');
        $taxRateId = (string) $this->params()->fromPost('taxRateId');
        $memberState = (string) $this->params()->fromPost('memberState');
        $view = $this->getJsonModelFactory()->newInstance();
        $this->getProductService()->saveProductTaxRateId($productId, $taxRateId, $memberState);
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
            $this->getProductService()->saveProductName($productId, $name)
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

    public function stockCsvExportCheckAction()
    {
        $this->checkUsage();

        $guid = uniqid('', true);
        $this->stockCsvService->startProgress($guid);
        return $this->getJsonModelFactory()->newInstance(
            ["allowed" => true, "guid" => $guid]
        );
    }

    public function stockCsvExportProgressAction()
    {
        $guid = $this->params()->fromPost(static::PROGRESS_KEY_NAME_STOCK_EXPORT);
        $count = $this->stockCsvService->checkProgress($guid);
        $total = $this->stockCsvService->getTotalForProgress($guid);
        return $this->getJsonModelFactory()->newInstance(
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

        $view = $this->getJsonModelFactory()->newInstance();
        $view->setVariable("success", true);
        return $view;
    }

    public function detailsUpdateAction()
    {
        $this->checkUsage();

        $view = $this->getJsonModelFactory()->newInstance();
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

    protected function checkUsage()
    {
        if ($this->usageService->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }
    }

    /**
     * @return self
     */
    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
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
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->productService;
    }

    /**
     * @return self
     */
    protected function setFilterMapper(FilterMapper $filterMapper)
    {
        $this->filterMapper = $filterMapper;
        return $this;
    }

    /**
     * @return FilterMapper
     */
    protected function getFilterMapper()
    {
        return $this->filterMapper;
    }

    /**
     * @return Translator
     */
    protected function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return self
     */
    protected function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * @return AccountService
     */
    protected function getAccountService()
    {
        return $this->accountService;
    }

    /**
     * @return self
     */
    public function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    /**
     * @param TaxRateService $taxRateService
     * @return self
     */
    public function setTaxRateService(TaxRateService $taxRateService)
    {
        $this->taxRateService = $taxRateService;
        return $this;
    }

    /**
     * @return self
     */
    public function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }

    /**
     * @return self
     */
    public function setStockCsvService(StockCsvService $stockCsvService)
    {
        $this->stockCsvService = $stockCsvService;
        return $this;
    }

    protected function setStockSettingsService(StockSettingsService $stockSettingsService)
    {
        $this->stockSettingsService = $stockSettingsService;
        return $this;
    }

    protected function setUsageService(UsageService $usageService)
    {
        $this->usageService = $usageService;
        return $this;
    }
}
