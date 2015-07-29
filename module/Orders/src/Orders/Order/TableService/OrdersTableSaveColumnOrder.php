<?php
namespace Orders\Order\TableService;

use CG_UI\View\DataTable;
use Zend\View\Model\ViewModel;

class OrdersTableSaveColumnOrder implements OrdersTableModifierInterface
{
    /** @var ViewModel $javascript */
    protected $javascript;

    public function __construct(ViewModel $javascript)
    {
        $this->setJavascript($javascript);
    }

    public function modifyTable(DataTable $ordersTable)
    {
        $ordersTable->addChild(
            $this->javascript,
            'javascript',
            true
        );
    }

    /**
     * @return self
     */
    protected function setJavascript(ViewModel $javascript)
    {
        $this->javascript = $javascript;
        return $this;
    }
} 
