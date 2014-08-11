<?php
namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Product\Entity as ProductEntity;
use CG\Http\Rpc\Exception as RpcException;
use ArrayObject;
use CG\Stdlib\PageLimit;
use CG\Stdlib\OrderBy;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Products\Product\Service as ProductService;
use Products\Product\BulkActions\Service as BulkActionsService;
use CG\Stock\Service as StockService;

class ProductsController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    protected $viewModelFactory;
    protected $productService;
    protected $stockService;
    protected $bulkActionsService;

    const SAVE_ROUTE = 'SAVE';

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ProductService $productService,
        BulkActionsService $bulkActionsService,
        StockService $stockService
    ) {
        $this->setViewModelFactory($viewModelFactory)
             ->setProductService($productService)
             ->setBulkActionsService($bulkActionsService)
             ->setStockService($stockService);
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->addChild($this->getDetailsSidebar(), 'sidebarLinks');

        $bulkActions = $this->getBulkActionsService()->getListPageBulkActions();
        $bulkAction = $this->getViewModelFactory()->newInstance()->setTemplate('products/products/bulk-actions/index');
        $bulkActions->addChild(
            $bulkAction,
            'afterActions'
        );
        $view->addChild($bulkActions, 'bulkItems');
        $view->addChild($this->getProductView(), 'products');

        $bulkAction->setVariable('isHeaderBarVisible', $this->getProductService()->isFilterBarVisible());
        $view->setVariable('isSidebarVisible', $this->getProductService()->isSidebarVisible());
        $view->setVariable('isHeaderBarVisible', $this->getProductService()->isFilterBarVisible());      
        return $view;
    }

    protected function getProductView()
    {
        try {
            $products = $this->getProductService()->fetchProducts();
            $view = $this->getViewModelFactory()->newInstance();
            $view->setTemplate('products/products/many');
            $productViews = [];
            foreach ($products as $product) {
                $productViews[] = $this->getSimpleProductView($product);
            }
            $productViews = array_reverse($productViews);
            foreach ($productViews as $productView) {
                $view->addChild($productView, 'products', true);
            }
            return $view;
        } catch (NotFound $e) {
            return $this->getNoProductsView();
        }
    }

    protected function getNoProductsView()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate('products/products/none');
        return $view;
    }

    protected function getSimpleProductView(ProductEntity$product)
    {
        $stockCollection = $product->getStock();
        $stockCollection->rewind();
        $stock = $stockCollection->current();
        $available = $stock->getTotalOnHand();
        $allocated = $stock->getTotalAllocated();
        $total = $stock->getTotalAvailable();

        $name = $product->getName();
        $sku = $product->getSku();
        $id = $product->getStock()->getId();
   
        foreach($product->getStock() as $stock)
        {
            $available = $stock->getTotalAvailable();
            $allocated = $stock->getTotalAllocated();
            $total = $stock->getTotalOnHand();;
        }

        $totalTextBox = $this->getViewModelFactory()->newInstance([
            'value' => $total,
            'name' => 'total-stock-' . $stock->getId()
        ]);

        $totalTextBox->setTemplate('elements/inline-text.mustache');

        $product = $this->getViewModelFactory()->newInstance([
            'title' => $product->getName(),
            'sku' => $product->getSku(),
            'available' => $available,
            'allocated' => $allocated
        ]);
        $product->setTemplate('elements/simple-product.mustache');
        $product->addChild($totalTextBox, 'total');
        return $product;
    }

    protected function getDetailsSidebar()
    {
        $sidebar = $this->getViewModelFactory()->newInstance();
        $sidebar->setTemplate('products/products/sidebar/navbar');

        $links = [];
        $sidebar->setVariable('links', $links);

        return $sidebar;
    }

    protected function getDefaultJsonData()
    {
        return new ArrayObject(
            [
                'iTotalRecords' => 0,
                'iTotalDisplayRecords' => 0,
                'sEcho' => (int) $this->params()->fromPost('sEcho'),
                'Records' => [],
                'sFilterId' => null,
            ]
        );
    }

    protected function getPageLimit()
    {
        $pageLimit = new PageLimit();

        if ($this->params()->fromPost('iDisplayLength') > 0) {
            $pageLimit
                ->setLimit($this->params()->fromPost('iDisplayLength'))
                ->setPageFromOffset($this->params()->fromPost('iDisplayStart'));
        }

        return $pageLimit;
    }

    protected function getOrderBy()
    {
        $orderBy = new OrderBy();

        $orderByIndex = $this->params()->fromPost('iSortCol_0');
        if ($orderByIndex) {
            $orderBy
                ->setColumn($this->params()->fromPost('mDataProp_' . $orderByIndex))
                ->setDirection($this->params()->fromPost('sSortDir_0', 'asc'));
        }

        return $orderBy;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return ViewModelFactory
     */
    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    protected function setBulkActionsService(BulkActionsService $bulkActionsService)
    {
        $this->bulkActionsService = $bulkActionsService;
        return $this;
    }

    /**
     * @return BulkActionsService
     */
    protected function getBulkActionsService()
    {
        return $this->bulkActionsService;
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

    protected function setStockService(StockService $stockService)
    {
        $this->stockService = $stockService;
        return $this;
    }

    protected function getStockService()
    {
        return $this->stockService;
    }
}