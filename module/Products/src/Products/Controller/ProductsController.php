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
use Zend\I18n\Translator\Translator;

class ProductsController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const DEFAULT_DISPLAY_VARIATIONS = 2;

    protected $viewModelFactory;
    protected $productService;
    protected $stockService;
    protected $bulkActionsService;
    protected $translator;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ProductService $productService,
        BulkActionsService $bulkActionsService,
        StockService $stockService,
        Translator $translator
    ) {
        $this->setViewModelFactory($viewModelFactory)
             ->setProductService($productService)
             ->setBulkActionsService($bulkActionsService)
             ->setStockService($stockService)
             ->setTranslator($translator);
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
        $view->addChild($this->getProductsView(), 'products');

        $bulkAction->setVariable('isHeaderBarVisible', $this->getProductService()->isFilterBarVisible());
        $view->setVariable('isSidebarVisible', $this->getProductService()->isSidebarVisible());
        $view->setVariable('isHeaderBarVisible', $this->getProductService()->isFilterBarVisible());      
        return $view;
    }

    protected function getProductsView()
    {
        try {
            $products = $this->getProductService()->fetchProducts();
            $view = $this->getViewModelFactory()->newInstance();
            $view->setTemplate('products/products/many');
            foreach ($products as $product) {
                $productView = $this->getProductView($product);
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

    protected function getProductView(ProductEntity $product)
    {
        $variations = $product->getVariations();
        $productView = $this->getViewModelFactory()->newInstance([
            'id' => $product->getId(),
            'title' => $product->getName(),
            'sku' => $product->getSku(),
            'status' => 'active',
            'hasVariations' => (count($variations) > 0)
        ]);
        $productView->setTemplate('elements/product.mustache');

        if (count($variations)) {
            $productView->addChild($this->getParentProductView($product, $variations), 'productContent');
            $productView->addChild($this->getExpandButtonView($product), 'expandButton');
        } else {
            $productView->addChild($this->getStandaloneProductView($product), 'productContent');
        }

        return $productView;
    }

    protected function getExpandButtonView(ProductEntity $product)
    {
        $buttonView = $this->getViewModelFactory()->newInstance([
            'buttons' => [
                'id' => 'product-variation-expand-button-'.$product->getId(),
                'class' => 'product-variation-expand-button',
                'value' => $this->getTranslator()->translate('Expand Variations'),
                'action' => $this->getTranslator()->translate('Contract Variations'),
            ]
        ]);
        $buttonView->setTemplate('elements/buttons.mustache');
        return $buttonView;
    }

    protected function getParentProductView(ProductEntity $product, ProductCollection $variations)
    {
        $parentView = $this->getViewModelFactory()->newInstance();
        $parentView->setTemplate('product/variationStock.mustache');
        $parentView->addChild($this->getVariationTableView($product, $variations), 'variationTable');
        $parentView->addChild($this->getStockTableView($product, $variations), 'stockTable');
        return $parentView;
    }

    protected function getStandaloneProductView(ProductEntity $product)
    {
        return $this->getStockTableView($product);
    }

    protected function getVariationTableView(ProductEntity $product, ProductCollection $variations)
    {
        $attributeNames = $product->getAttributeNames();
        $variationsView = $this->getViewModelFactory()->newInstance([
            'attributes' => $attributeNames
        ]);
        $variationsView->setTemplate('product/variationTable.mustache');

        foreach ($variations as $variation) {
            $attributeValues = [];
            foreach ($attributeNames as $attributeName) {
                $attributeValues[] = $variation->getAttributeValue($attributeName);
            }
            $viewParams = [
                'sku' => $variation->getSku(),
                'attributes' => $attributeValues
            ];
            $variationView = $this->getViewModelFactory()->newInstance($viewParams);
            $variationView->setTemplate('product/variationRow.mustache');
            $variationsView->addChild($variationView, 'variations', true);
            if ($variation->getStock() && count($variation->getStock()->getLocations()) > 1) {
                for ($count = 1; $count < count($variation->getStock()->getLocations()); $count++) {
                    $viewParams = [
                        'sku' => '',
                        'attributes' => array_fill(0, count($attributeNames), '')
                    ];
                    $variationView = $this->getViewModelFactory()->newInstance($viewParams);
                    $variationView->setTemplate('product/variationRow.mustache');
                    $variationsView->addChild($variationView, 'variations', true);
                }
            }
        }

        return $variationsView;
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
            if (!$stock) {
                continue;
            }
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

    protected function getTranslator()
    {
        return $this->translator;
    }

    protected function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
        return $this;
    }
}