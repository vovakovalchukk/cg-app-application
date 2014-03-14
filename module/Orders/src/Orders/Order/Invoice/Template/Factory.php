<?php
namespace Orders\Order\Invoice\Template;

use Zend\Di\Di;
use CG\Order\Shared\Entity as Order;
use CG\Template\InvoiceEntity;
use CG\Template\Element\Text;
use CG\Template\FontFamily;

class Factory
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * @param Order $order
     * @return Template
     */
    public function getTemplateForOrderEntity(Order $order)
    {
        return $this->getDi()->get(
            InvoiceEntity::class, [
                'organisationUnitId' => 1
            ]
        );
    }
}