<?php
namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Product\Entity as ProductEntity;
use CG\Product\Collection as ProductCollection;
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

        $bulkAction->setVariable('isHeaderBarVisible', $this->getProductService()->isFilterBarVisible());
        $view->setVariable('isSidebarVisible', $this->getProductService()->isSidebarVisible());
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        return $view;
    }

    protected function getProductView(ProductEntity $product)
    {
        $productView = $this->getViewModelFactory()->newInstance([
            'title' => $product->getName(),
            'sku' => $product->getSku(),
            'status' => 'active'
        ]);
        $productView->setTemplate('elements/product.mustache');
        $variations = $product->getVariations();

        if (count($variations)) {
            $productView->addChild($this->getParentProductView($product, $variations), 'productContent');
        } else {
            $productView->addChild($this->getStandaloneProductView($product), 'productContent');
        }

        return $productView;
    }

    protected function getParentProductView(ProductEntity $product, ProductCollection $variations)
    {
        $parentView = $this->getViewModelFactory()->newInstance();
        $parentView->setTemplate('elements/variationStock.mustache');
        $parentView->addChild($this->getVariationTableView($product, $variations), 'variationTable');
        $parentView->addChild($this->getStockTableView($product, $variations), 'stockTable');
    }

    protected function getStandaloneProductView(ProductEntity $product)
    {
        return $this->getStockTableView($product);
    }

    protected function getVariationTableView(ProductEntity $product, ProductCollection $variations)
    {
        $variationsView = $this->getViewModelFactory()->newInstance([
            'attributes' => $product->getAttributeNames()
        ]);
        $variationsView->setTemplate('product/variationTable.mustache');

        foreach ($variations as $variation) {
            $viewParams = [
                'sku' => $variation->getSku(),
                'attributes' => array_values($variation->getAttributeValues())
            ];
            $variationView = $this->getViewModelFactory()->newInstance($viewParams);
            $variationView->setTemplate('product/variationRow.mustache');
            $variationView->addChild($variationView, 'variations', true);
        }

        return $variationView;
    }

    protected function getStockTableView(ProductEntity $product, ProductCollection $variations = null)
    {
        $stockView = $this->getViewModelFactory()->newInstance();
        $stockView->setTemplate('product/stockTable.mustache');

        if (!$variations) {
            $variations = [$product];
        }
        foreach ($variations as $variation) {
            $stock = $variation->getStock();
            foreach ($stock->getLocations() as $stockLocation) {
                $name = 'total-stock-' . $stock->getId();
                $totalView = $this->getViewModelFactory()->newInstance([
                    'value' => $stockLocation->getOnHand(),
                    'name' => $name,
                    'type' => 'number'
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
                $stockView->addChild($stockLocationView, 'stockLocations', true);
            }
        }

        return $stockView;
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
