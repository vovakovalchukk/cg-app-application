<?php

namespace Products\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use Products\Product\Service as ProductService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\Product\Entity as ProductEntity;
use CG\Product\Filter\Mapper as FilterMapper;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Http\StatusCode;
use Zend\I18n\Translator\Translator;
use CG\Account\Client\Service as AccountService;
use Products\Product\TaxRate\Service as TaxRateService;

class ProductsJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';
    const ROUTE_AJAX_TAX_RATE = 'tax_rate';
    const ROUTE_STOCK_UPDATE = 'stockupdate';
    const ROUTE_DELETE = 'Delete';

    protected $productService;
    protected $jsonModelFactory;
    protected $filterMapper;
    protected $translator;
    protected $accountService;
    protected $taxRateService;

    public function __construct(
        ProductService $productService,
        JsonModelFactory $jsonModelFactory,
        FilterMapper $filterMapper,
        Translator $translator,
        AccountService $accountService,
        TaxRateService $taxRateService
    ) {
        $this->setProductService($productService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setFilterMapper($filterMapper)
            ->setTranslator($translator)
            ->setAccountService($accountService)
            ->setTaxRateService($taxRateService);
    }

    public function ajaxAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        $filterParams = $this->params()->fromPost('filter', []);
        if (!array_key_exists('deleted', $filterParams)) {
            $filterParams['deleted'] = false;
        }
        $requestFilter = $this->getFilterMapper()->fromArray($filterParams);
        $productsArray = [];
        try {
            $products = $this->getProductService()->fetchProducts($requestFilter);
            $accounts = $this->getAccountsIndexedById($requestFilter->getOrganisationUnitId());

            foreach ($products as $product) {
                $productsArray[] = $this->toArrayProductEntityWithEmbeddedData($product, $accounts);
            }
        } catch(NotFound $e) {
            //noop
        }
        return $view->setVariable('products', $productsArray);
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

    protected function toArrayProductEntityWithEmbeddedData(ProductEntity $productEntity, $accounts)
    {
        $product = $productEntity->toArray();

        $product = array_merge($product, [
            'images' => $productEntity->getImages()->toArray(),
            'listings' => $productEntity->getListings()->toArray(),
            'accounts' => $accounts
        ]);

        $product['taxRates'] = $this->getTaxRateService()->getTaxRatesOptionsForProduct($productEntity);

        foreach ($productEntity->getVariations() as $variation) {
            $product['variations'][] = $this->toArrayProductEntityWithEmbeddedData($variation, $accounts);
        }

        if (!$productEntity->getStock() || count($productEntity->getVariations())) {
            return $product;
        }

        $stockEntity = $productEntity->getStock();
        $product['stock'] = array_merge($productEntity->getStock()->toArray(), [
            'locations' => $stockEntity->getLocations()->toArray()
        ]);

        foreach ($product['stock']['locations'] as $stockLocationIndex => $stockLocation) {
            $stockLocationId = $product['stock']['locations'][$stockLocationIndex]['id'];
            $product['stock']['locations'][$stockLocationIndex]['eTag'] = $stockEntity->getLocations()->getById($stockLocationId)->getEtag();
        }
        return $product;
    }

    public function stockUpdateAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        try {
            $stockLocation = $this->getProductService()->updateStock(
                $this->params()->fromPost('stockLocationId'),
                $this->params()->fromPost('eTag'),
                $this->params()->fromPost('totalQuantity')
            );
            $view->setVariable('eTag', $stockLocation->getETag());
        } catch (NotModified $e) {
            $view->setVariable('code', StatusCode::NOT_MODIFIED);
            $view->setVariable('message', $this->getTranslator()->translate('There were no changes to be saved'));
        }

        return $view;
    }

    public function deleteAction()
    {
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
        $productId = (int) $this->params()->fromPost('productId');
        $taxRateId = (string) $this->params()->fromPost('taxRateId');
        $view = $this->getJsonModelFactory()->newInstance();
        $this->getProductService()->saveProductTaxRateId($productId, $taxRateId);
        $view->setVariable('saved', true);
        return $view;
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    protected function getProductService()
    {
        return $this->productService;
    }

    protected function setFilterMapper(FilterMapper $filterMapper)
    {
        $this->filterMapper = $filterMapper;
        return $this;
    }

    protected function getFilterMapper()
    {
        return $this->filterMapper;
    }

    protected function getTranslator()
    {
        return $this->translator;
    }

    protected function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    protected function getAccountService()
    {
        return $this->accountService;
    }

    public function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    /**
     * @return TaxRateService
     */
    public function getTaxRateService()
    {
        return $this->taxRateService;
    }

    /**
     * @param TaxRateService $taxRateService
     * @return $this
     */
    public function setTaxRateService(TaxRateService $taxRateService)
    {
        $this->taxRateService = $taxRateService;
        return $this;
    }
}
