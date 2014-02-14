<?php
namespace Orders\Order\TableService;

use CG_UI\View\DataTable;
use Zend\View\Model\ViewModel;

class OrdersTableMustacheFormatters implements OrdersTableModifierInterface
{
    protected $javascript;

    public function __construct(ViewModel $javascript)
    {
        $this->setJavascript($javascript);
    }

    public function setJavascript(ViewModel $javascript)
    {
        $this->javascript = $javascript;
        return $this;
    }

    /**
     * @return ViewModel
     */
    public function getJavascript()
    {
        return $this->javascript;
    }

    public function modifyTable(DataTable $ordersTable)
    {
        $ordersTable->addChild(
            $this->getJavascript(),
            'javascript',
            true
        );
    }
} 