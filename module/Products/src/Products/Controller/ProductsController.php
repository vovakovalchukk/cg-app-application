<?php
namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Stdlib\Exception\Runtime\NotFound;
use DirectoryIterator;
use CG\Http\Rpc\Exception as RpcException;
use ArrayObject;
use CG\Stdlib\PageLimit;
use CG\Stdlib\OrderBy;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Products\Product\Service as ProductService;
use Products\Product\BulkActions\Service as BulkActionsService;

class ProductsController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    protected $viewModelFactory;
    protected $productService;
    protected $bulkActionsService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ProductService $productService,
        BulkActionsService $bulkActionsService
    ) {
        $this->setViewModelFactory($viewModelFactory)
             ->setProductService($productService)
             ->setBulkActionsService($bulkActionsService);
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
            echo "No products found";
        }
    }

    protected function getSimpleProductView($product)
    {
        $name = $product->getName();
        $sku = $product->getSku();
        $total = 45;
        $allocated = 10;
        $available = $total - $allocated;

        $product = $this->getViewModelFactory()->newInstance([
            'title' => $name,
            'SKU' => $sku,
            'available' => $available,
            'allocated' => $allocated,
            'total' => $total
        ]);
        $product->setTemplate('elements/simple-product.mustache');

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

    protected function setStoredFiltersService(StoredFiltersService $storedFiltersService)
    {
        $this->storedFiltersService = $storedFiltersService;
        return $this;
    }

    /**
      @return StoredFiltersService
    */
    protected function getStoredFiltersService()
    {
        return $this->storedFiltersService;
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
}