<?php
namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Product\Entity as ProductEntity;
use CG\Http\Rpc\Exception as RpcException;
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
            foreach ($products as $product) {
                $productView = $this->getSimpleProductView($product);
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

    protected function getSimpleProductView(ProductEntity $product)
    {
        $productView = $this->getViewModelFactory()->newInstance([
            'title' => $product->getName(),
            'sku' => $product->getSku(),
            'status' => 'active'
        ]);
        $productView->setTemplate('elements/simpleProduct.mustache');
        $stock = $product->getStock();
        foreach($stock->getLocations() as $stockLocation) {
            $name = 'total-stock-' . $stock->getId();
            $totalView = $this->getViewModelFactory()->newInstance([
                'value' => $stockLocation->getOnHand(),
                'name' => $name
            ]);
            $totalView->setTemplate('elements/inline-text.mustache');
            $stockLocationView = $this->getViewModelFactory()->newInstance([
                'available' => $stockLocation->getAvailable(),
                'allocated' => $stockLocation->getAllocated(),
                'total' => $totalView,
                'totalName' => $name,
                'stockLocationId' => $stockLocation->getId(),
                'eTag' => $stockLocation->getEtag()
            ]);
            $stockLocationView->setTemplate('product/stockRow.mustache');
            $productView->addChild($stockLocationView, 'stockLocations', true);
        }
        return $productView;
    }

    protected function getDetailsSidebar()
    {
        $sidebar = $this->getViewModelFactory()->newInstance();
        $sidebar->setTemplate('products/products/sidebar/navbar');

        $links = [];
        $sidebar->setVariable('links', $links);

        return $sidebar;
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