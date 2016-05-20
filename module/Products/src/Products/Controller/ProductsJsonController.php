<?php

namespace Products\Controller;

use CG\Account\Client\Service as AccountService;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Http\StatusCode;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\Entity as ProductEntity;
use CG\Product\Filter\Mapper as FilterMapper;
use CG\Listing\Entity as ListingEntity;
use CG\Listing\StatusHistory\Entity as ListingStatusHistory;
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
        try {
            $products = $this->getProductService()->fetchProducts($requestFilter, $limit, $page);
            $organisationUnitIds = $requestFilter->getOrganisationUnitId();
            $accounts = $this->getAccountsIndexedById($organisationUnitIds);
            $rootOrganisationUnit = $this->organisationUnitService->getRootOuFromOuId(reset($organisationUnitIds));
            $isVatRegistered = $rootOrganisationUnit->isVatRegistered();

            foreach ($products as $product) {
                $productsArray[] = $this->toArrayProductEntityWithEmbeddedData($product, $accounts, $isVatRegistered);
            }
            $total = $products->getTotal();
        } catch(NotFound $e) {
            //noop
        }
        $view->setVariable('products', $productsArray)
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

    protected function toArrayProductEntityWithEmbeddedData(ProductEntity $productEntity, $accounts, $isVatRegistered)
    {
        $product = $productEntity->toArray();

        $product = array_merge($product, [
            'eTag' => $productEntity->getStoredETag(),
            'images' => $productEntity->getImages()->toArray(),
            'listings' => $this->getProductListingsArray($productEntity),
            'accounts' => $accounts,
            'stockModeDefault' => $this->stockSettingsService->getStockModeDefault(),
            'stockModeDesc' => $this->stockSettingsService->getStockModeDecriptionForProduct($productEntity),
            'stockModeOptions' => $this->stockSettingsService->getStockModeOptionsForProduct($productEntity),
            'stockLevel' => $this->stockSettingsService->getStockLevelForProduct($productEntity),
        ]);

        if($isVatRegistered) {
            $product['taxRates'] = $this->taxRateService->getTaxRatesOptionsForProduct($productEntity);
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
        $product['details'] = $detailsEntity ? $detailsEntity->toArray() : ['sku' => $productEntity->getSku()];

        foreach ($product['stock']['locations'] as $stockLocationIndex => $stockLocation) {
            $stockLocationId = $product['stock']['locations'][$stockLocationIndex]['id'];
            $product['stock']['locations'][$stockLocationIndex]['eTag'] = $stockEntity->getLocations()->getById($stockLocationId)->getStoredETag();
        }
        return $product;
    }

    protected function getProductListingsArray(ProductEntity $productEntity)
    {
        $listings = [];
        /** @var ListingEntity $listing */
        foreach ($productEntity->getListings() as $listing) {
            $listingData = $listing->toArray();
            $listingData['message'] = '';

            $statusHistory = $listing->getStatusHistory();
            $statusHistory->rewind();

            if ($statusHistory->count() == 0) {
                $listings[] = $listingData;
                continue;
            }

            /** @var ListingStatusHistory $currentStatus */
            $currentStatus = $statusHistory->current();
            if ($currentStatus->getStatus() != $listing->getStatus()) {
                $listings[] = $listingData;
                continue;
            }

            $listingData['message'] = $currentStatus->getMessage();
            $listings[] = $listingData;
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

    public function deleteAction()
    {
        $this->checkUsage();

        $view = $this->getJsonModelFactory()->newInstance();

        $productIds = $this->params()->fromPost('productIds');
        if (empty($productIds)){
            $view->setVariable('deleted', false);
            return $view;
        }

        $this->getProductService()->deleteProductsById($productIds);
        $view->setVariable('deleted', true);
        return $view;
    }

    public function saveProductTaxRateAction()
    {
        $this->checkUsage();

        $productId = (int) $this->params()->fromPost('productId');
        $taxRateId = (string) $this->params()->fromPost('taxRateId');
        $view = $this->getJsonModelFactory()->newInstance();
        $this->getProductService()->saveProductTaxRateId($productId, $taxRateId);
        $view->setVariable('saved', true);
        return $view;
    }

    public function saveProductStockModeAction()
    {
        $this->checkUsage();

        $productId = $this->params()->fromPost('id');
        $eTag = $this->params()->fromPost('eTag');
        $stockMode = $this->params()->fromPost('stockMode');
        if ($stockMode === 'null') {
            $stockMode = null;
        }
        $product = $this->stockSettingsService->saveProductStockMode($productId, $stockMode, $eTag);
        $data = [
            'eTags' => [$product->getId() => $product->getStoredEtag()],
            'stockModeDefault' => $this->stockSettingsService->getStockModeDefault(),
            'stockModeDesc' => $this->stockSettingsService->getStockModeDecriptionForProduct($product),
            'stockLevel' => $this->stockSettingsService->getStockLevelForProduct($product),
        ];
        foreach ($product->getVariations() as $variation) {
            $data['eTags'][$variation->getId()] = $variation->getStoredEtag();
        }
        return $this->jsonModelFactory->newInstance($data);
    }

    public function saveProductStockLevelAction()
    {
        $this->checkUsage();

        $productId = $this->params()->fromPost('id');
        $eTag = $this->params()->fromPost('eTag');
        $stockLevel = $this->params()->fromPost('stockLevel');

        $affectedProducts = $this->stockSettingsService->saveProductStockLevel($productId, $stockLevel, $eTag);
        $data = ['eTags' => []];
        foreach ($affectedProducts as $product) {
            $data['eTags'][$product->getId()] = $product->getStoredEtag();
        }
        return $this->jsonModelFactory->newInstance($data);
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
