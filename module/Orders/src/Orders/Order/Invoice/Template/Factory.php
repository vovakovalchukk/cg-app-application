<?php
namespace Orders\Order\Invoice\Template;

use Zend\Di\Di;
use CG\Order\Shared\Entity as Order;
use CG\Template\Entity as Template;

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
            Template::class,
            [
                'type' => 'Invoice',
                'organisationUnitId' => 0,
                'minHeight' => 842,
                'minWidth' => 592,
                'elements' => [],
                'id' => 0
            ]
        );
    }
}