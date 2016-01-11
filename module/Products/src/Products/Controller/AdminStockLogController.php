<?php
namespace Products\Controller;

use CG\Product\Client\Service as ProductService;
use CG\Product\Entity as Product;
use CG\User\ActiveUserInterface;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\Table\Cell as TableCell;
use CG_UI\View\Table\Cell\Collection as TableCells;
use CG_UI\View\Table\Column as TableColumn;
use CG_UI\View\Table\Row\Collection as TableRows;
use CG_UI\View\Table\Row as TableRow;
use CG_UI\View\Table;
use CG_UI\View\Table\Cell;
use Products\Module;
use Zend\Db\Sql\Sql;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * WARNING: This controller is a complete hack and should be replaced when we implement stock logs properly
 */
class AdminStockLogController extends AbstractActionController
{
    const ROUTE_STOCKLOG = 'StockLog Popup';

    /** @var Sql $sql */
    protected $sql;
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var ProductService $productService */
    protected $productService;

    public function __construct(
        Sql $sql,
        ActiveUserInterface $activeUserContainer,
        ViewModelFactory $viewModelFactory,
        ProductService $productService
    ) {
        $this
            ->setSql($sql)
            ->setActiveUserContainer($activeUserContainer)
            ->setViewModelFactory($viewModelFactory)
            ->setProductService($productService);
    }

    public function indexAction()
    {
        if (!$this->activeUserContainer->isAdmin()) {
            return $this->notFoundAction();
        }

        $productId = $this->params('productId');
        return $this->createViewModel(['isHeaderBarVisible' => false, 'isSidebarVisible' => false])
            ->addChild($this->getProductInfo($productId), 'productInfo')
            ->addChild($this->getStockLogTable($productId), 'stockLog')
            ->addChild($this->getStockAdjustmentLogTable($productId), 'stockAdjustmentLog');
    }

    /**
     * @return ViewModel
     */
    protected function createViewModel($variables = null, $options = null)
    {
        return $this->viewModelFactory->newInstance($variables, $options);
    }

    protected function getProductInfo($productId)
    {
        /** @var Product $product */
        $product = $this->productService->fetch($this->params('productId'));
        $variation = null;

        if ($product->isVariation()) {
            $variation = $product;
            $product = $this->productService->fetch($product->getParentProductId());
        }

        return $this
            ->createViewModel(
                [
                    'product' => $product,
                    'variation' => $variation,
                    'route' => implode('/', [Module::ROUTE, static::ROUTE_STOCKLOG]),
                ]
            )
            ->setTemplate('products/admin-stock-log/productInfo');
    }

    protected function getStockLogTable($productId)
    {
        return $this->createTable('stockLog', $productId);
    }

    protected function getStockAdjustmentLogTable($productId)
    {
        return $this->createTable('stockAdjustmentLog', $productId);
    }

    /**
     * @return Table
     */
    protected function createTable($tableName, $productId)
    {
        $select = $this->sql
            ->select(['sl' => $tableName])
            ->join(['p' => 'product'], 'sl.organisationUnitId = p.organisationUnitId AND sl.sku = p.sku', [])
            ->where(['p.id' => $productId])
            ->order(['date DESC', 'time DESC']);

        $results = $this->sql->prepareStatementForSqlObject($select)->execute();

        $table = new Table();
        foreach ($results->getResource()->result_metadata()->fetch_fields() as $column) {
            $table->addColumn(new TableColumn(ucfirst($column->name), $column->name));
        }

        $rows = new TableRows();
        if ($results->getAffectedRows() > 0) {
            foreach ($results as $result) {
                $cells = new TableCells();
                foreach ($result as $cell) {
                    $cells->attach(new Cell($cell));
                }
                $rows->attach(new TableRow($cells));
            }
        } else {
            $cells = new TableCells();
            $cells->attach(new Cell('No logs', null, $results->getFieldCount()));
            $rows->attach(new TableRow($cells));
        }
        $table->setRows($rows);

        return $table;
    }

    /**
     * @return self
     */
    protected function setSql(Sql $sql)
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return self
     */
    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return self
     */
    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return self
     */
    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }
} 
